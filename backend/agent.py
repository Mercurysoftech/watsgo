from flask import Flask, request, jsonify
from flask_cors import CORS
from datetime import datetime, timedelta
import os, re, requests, razorpay, json, threading, time, pymysql
from dotenv import load_dotenv
from pymysql.cursors import DictCursor

# ---------------------------
# Load env (only for database connection)
# ---------------------------
load_dotenv()
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# ---------------------------
# Global Variables
# ---------------------------
razorpay_client = None
razorpay_key = None
razorpay_secret = None
YOUR_PHONE_NUMBER_ID = None
YOUR_FLOW_ID = None
ACCESS_TOKEN = None
WHATSAPP_API_URL = None
FB_CATALOG_ID = None
CONSUMER_KEY = None
CONSUMER_SECRET = None
store_url = None

# ---------------------------
# MySQL Database Connection
# ---------------------------

def get_db_connection():
    try:
        return pymysql.connect(
            host=os.getenv("DB_HOST", "127.0.0.1"),
            user=os.getenv("DB_USER", "MercurySoftech_whatsappbot"),
            password=os.getenv("DB_PASSWORD", "Mercury@2025"),
            database=os.getenv("DB_NAME", "MercurySoftech_whatsappbot"),
            cursorclass=DictCursor
        )
    except Exception as e:
        print(f"❌ Database connection error: {e}")
        return None

def get_config_value(key, default=None):
    """Get configuration value from users table"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return default
            
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM users WHERE id = 2 LIMIT 1")
            result = cursor.fetchone()
            if result and key in result:
                return result[key]
            return default
    except Exception as e:
        print(f"Error getting config value for {key}: {e}")
        return default
    finally:
        if connection:
            connection.close()

def initialize_configuration():
    """Initialize Razorpay + WhatsApp configuration from database"""
    global razorpay_key, razorpay_secret, YOUR_PHONE_NUMBER_ID, YOUR_FLOW_ID
    global ACCESS_TOKEN, WHATSAPP_API_URL, FB_CATALOG_ID, CONSUMER_KEY, CONSUMER_SECRET
    global store_url, razorpay_client

    # Pull values from users table
    razorpay_key = get_config_value("razorpay_key_id", os.getenv("RAZORPAY_KEY_ID"))
    razorpay_secret = get_config_value("razorpay_key_secret", os.getenv("RAZORPAY_KEY_SECRET"))
    YOUR_PHONE_NUMBER_ID = get_config_value("wa_phone_id", os.getenv("WA_PHONE_ID"))
    YOUR_FLOW_ID = get_config_value("flow_id")
    ACCESS_TOKEN = get_config_value("com_access_token", os.getenv("COM_ACCESS_TOKEN"))
    FB_CATALOG_ID = get_config_value("catalog_id")
    CONSUMER_KEY = get_config_value("consumer_key")
    CONSUMER_SECRET = get_config_value("consumer_secret")
    store_url = get_config_value("store_url")

    # Debug log
    print("🔑 Razorpay Key:", razorpay_key)
    print("📱 WhatsApp Phone ID:", YOUR_PHONE_NUMBER_ID)
    print("📋 Catalog ID:", FB_CATALOG_ID)
    print("🚀 Flow ID:", YOUR_FLOW_ID)
    print("🧾 Access Token:", ACCESS_TOKEN)
    print("consumer_key:", CONSUMER_KEY)
    print("consumer_secret:", CONSUMER_SECRET)
    print("store_url:", store_url)

    # Build WhatsApp API URL
    WHATSAPP_API_URL = (
        f"https://graph.facebook.com/v21.0/{YOUR_PHONE_NUMBER_ID}/messages"
        if YOUR_PHONE_NUMBER_ID else None
    )

    # Initialize Razorpay client
    if razorpay_key and razorpay_secret:
        try:
            razorpay_client = razorpay.Client(auth=(razorpay_key, razorpay_secret))
            print("✅ Razorpay client initialized")
        except Exception as e:
            print(f"❌ Failed to init Razorpay client: {e}")
            razorpay_client = None
    else:
        print("⚠️ Razorpay credentials not found in users table")
        razorpay_client = None

# ---------------------------
# Cache Management (using MySQL)
# ---------------------------
def get_user_cache(phone_number, cache_key):
    """Get cached data for a user"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return None
            
        with connection.cursor() as cursor:
            cursor.execute(
                "SELECT cache_data FROM user_cache WHERE phone_number = %s AND cache_key = %s",
                (phone_number, cache_key)
            )
            result = cursor.fetchone()
            return json.loads(result['cache_data']) if result and result['cache_data'] else None
    except Exception as e:
        print(f"Error getting cache: {e}")
        return None
    finally:
        if connection:
            connection.close()

def set_user_cache(phone_number, cache_key, cache_data):
    """Set cached data for a user"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return
            
        with connection.cursor() as cursor:
            # Use INSERT ... ON DUPLICATE KEY UPDATE for upsert operation
            cursor.execute("""
                INSERT INTO user_cache (phone_number, cache_key, cache_data) 
                VALUES (%s, %s, %s)
                ON DUPLICATE KEY UPDATE cache_data = %s, updated_at = CURRENT_TIMESTAMP
            """, (phone_number, cache_key, json.dumps(cache_data), json.dumps(cache_data)))
            connection.commit()
    except Exception as e:
        print(f"Error setting cache: {e}")
    finally:
        if connection:
            connection.close()

def clean_old_cache():
    """Background task to clean old cache entries"""
    while True:
        try:
            connection = get_db_connection()
            if not connection:
                time.sleep(300)
                continue
                
            with connection.cursor() as cursor:
                # Delete cache entries older than 24 hours
                cursor.execute("DELETE FROM user_cache WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)")
                connection.commit()
                print(f"Cleaned {cursor.rowcount} old cache entries")
            time.sleep(3600)  # Run every hour
        except Exception as e:
            print(f"Error cleaning cache: {e}")
            time.sleep(300)

# ---------------------------
# Transaction Management
# ---------------------------
def save_transaction(transaction_data):
    """Save transaction to MySQL database"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return None
            
        with connection.cursor() as cursor:
            sql = """
                INSERT INTO transactions 
                (user, status, payment_id, payment_link_id, order_id, total_amount, order_summary, address_info, customer_info)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            cursor.execute(sql, (
                transaction_data["user"],
                transaction_data["status"],
                transaction_data.get("payment_id"),
                transaction_data.get("payment_link_id"),
                transaction_data.get("order_id"),
                transaction_data["total_amount"],
                transaction_data["order_summary"],
                json.dumps(transaction_data.get("address_info", {})),
                json.dumps(transaction_data.get("customer_info", {}))
            ))
            connection.commit()
            return cursor.lastrowid
    except Exception as e:
        print(f"Error saving transaction: {e}")
        return None
    finally:
        if connection:
            connection.close()

def get_transaction_by_field(field, value):
    """Get transaction by any field"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return None
            
        with connection.cursor() as cursor:
            cursor.execute(f"SELECT * FROM transactions WHERE {field} = %s ORDER BY created_at DESC LIMIT 1", (value,))
            return cursor.fetchone()
    except Exception as e:
        print(f"Error getting transaction: {e}")
        return None
    finally:
        if connection:
            connection.close()

def get_user_transactions(phone_number, limit=20):
    """Get all transactions for a user"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return []
            
        with connection.cursor() as cursor:
            cursor.execute(
                "SELECT * FROM transactions WHERE user = %s ORDER BY created_at DESC LIMIT %s",
                (phone_number, limit)
            )
            return cursor.fetchall()
    except Exception as e:
        print(f"Error getting user transactions: {e}")
        return []
    finally:
        if connection:
            connection.close()

def update_transaction_status(transaction_id, status, payment_id=None):
    """Update transaction status"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return False
            
        with connection.cursor() as cursor:
            if payment_id:
                cursor.execute(
                    "UPDATE transactions SET status = %s, payment_id = %s, updated_at = CURRENT_TIMESTAMP WHERE id = %s",
                    (status, payment_id, transaction_id)
                )
            else:
                cursor.execute(
                    "UPDATE transactions SET status = %s, updated_at = CURRENT_TIMESTAMP WHERE id = %s",
                    (status, transaction_id)
                )
            connection.commit()
            return cursor.rowcount > 0
    except Exception as e:
        print(f"Error updating transaction: {e}")
        return False
    finally:
        if connection:
            connection.close()

# ---------------------------
# Background Tasks
# ---------------------------
def check_pending_payments():
    """Background task to check status of pending payments"""
    while True:
        try:
            connection = get_db_connection()
            if not connection:
                time.sleep(60)
                continue
                
            with connection.cursor() as cursor:
                cursor.execute(
                    "SELECT * FROM transactions WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
                )
                pending_txns = cursor.fetchall()
                
                for txn in pending_txns:
                    payment_id = txn.get("payment_id")
                    if payment_id:
                        status = check_payment_status(payment_id)
                        if status != "pending":
                            update_transaction_status(txn["id"], status)
                            print(f"Updated pending payment {payment_id} → {status}")
            time.sleep(300)  # Check every 5 minutes
        except Exception as e:
            print(f"Error in pending payments check: {e}")
            time.sleep(60)

# ---------------------------
# Utility Functions
# ---------------------------
def clean_price(price_string):
    cleaned = re.sub(r"[^\d.]", "", str(price_string))
    try:
        return float(cleaned)
    except:
        return 0

def check_payment_status(payment_id):
    try:
        if not payment_id or not isinstance(payment_id, str) or not payment_id.startswith('pay_'):
            return "invalid_id"
        
        # Reinitialize razorpay client in case credentials changed
        initialize_configuration()
        
        if not razorpay_client:
            return "error"
            
        payment = razorpay_client.payment.fetch(payment_id)
        return payment.get("status", "pending")
    except Exception as e:
        print(f"Error checking payment status: {e}")
        return "error"

def send_whatsapp_message(to_number, message):
    # Reinitialize config in case values changed
    initialize_configuration()
    
    if not ACCESS_TOKEN or not YOUR_PHONE_NUMBER_ID:
        print("❌ WhatsApp not configured properly")
        return None
        
    headers = {
        "Authorization": f"Bearer {ACCESS_TOKEN}",
        "Content-Type": "application/json"
    }
    data = {
        "messaging_product": "whatsapp",
        "to": to_number,
        "type": "text",
        "text": {"body": message}
    }
    try:
        response = requests.post(WHATSAPP_API_URL, headers=headers, json=data, timeout=10)
        print(f"📤 Sending WhatsApp message to {to_number}: {message}")
        print(f"✅ WhatsApp API response: {response.status_code} {response.text}")
        return response.json()
    except Exception as e:
        print(f"❌ Error sending WhatsApp message: {e}")
        return None
    
def send_delivery_flow(to_number, flow_data=None):
    # Reinitialize config in case values changed
    initialize_configuration()
    
    if not ACCESS_TOKEN or not YOUR_PHONE_NUMBER_ID or not YOUR_FLOW_ID:
        print("❌ WhatsApp flow not configured properly")
        return {"error": "WhatsApp not configured"}
        
    headers = {
        "Authorization": f"Bearer {ACCESS_TOKEN}",
        "Content-Type": "application/json"
    }
    flow_token_data = {
        "screen": "shipping_form",
        "order_data": flow_data or {}
    }
    payload = {
        "messaging_product": "whatsapp",
        "recipient_type": "individual",
        "to": to_number,
        "type": "interactive",
        "interactive": {
            "type": "flow",
            "header": {"type": "text", "text": "📦 Delivery Information"},
            "body": {"text": "Please provide your delivery details to complete your order."},
            "footer": {"text": "Powered by MercuryBot 🚀"},
            "action": {
                "name": "flow",
                "parameters": {
                    "flow_message_version": "3",
                    "flow_action": "navigate",
                    "flow_id": YOUR_FLOW_ID,
                    "flow_cta": "Enter Delivery Details",
                    "flow_token": json.dumps(flow_token_data)
                }
            }
        }
    }
    try:
        response = requests.post(WHATSAPP_API_URL, headers=headers, json=payload)
        print(f"Flow response: {response.status_code} {response.text}")
        return response.json()
    except Exception as e:
        print(f"Error sending WhatsApp flow: {e}")
        return {"error": str(e)}

def sanitize_for_razorpay(value, max_length=200):
    """
    Keep only ASCII-safe characters (no emojis, no Tamil/Unicode),
    Razorpay MySQL rejects utf8mb4 in `notes`.
    """
    if not value:
        return ""
    # Remove emojis & non-ASCII characters
    clean = re.sub(r'[^\x00-\x7F]+', '', str(value))
    return clean[:max_length]

# ---------------------------
# WooCommerce Functions
# ---------------------------
def get_wc_categories():
    """Fetch WooCommerce categories"""
    try:
        store_url = get_config_value("store_url") 
        if not store_url:
            return []
            
        wc_url = f"{store_url}/wp-json/wc/v3/products/categories"
        consumer_key = get_config_value("consumer_key")
        consumer_secret = get_config_value("consumer_secret")

        res = requests.get(wc_url, auth=(consumer_key, consumer_secret), timeout=10)
        data = res.json()
        
        if isinstance(data, list) and data:
            categories = [{"id": c.get("id"), "name": c.get("name")} for c in data]
            return categories
        else:
            return []
    except Exception as e:
        print(f"❌ Error fetching WooCommerce categories: {e}")
        return []
    
def get_wc_products(category_id):
    """Fetch WooCommerce products by category ID"""
    try:
        store_url = get_config_value("store_url") 
        if not store_url:
            return []
            
        wc_url = f"{store_url}/wp-json/wc/v3/products"
        consumer_key = get_config_value("consumer_key")
        consumer_secret = get_config_value("consumer_secret")

        res = requests.get(
            wc_url,
            params={"category": category_id, "per_page": 50},
            auth=(consumer_key, consumer_secret),
            timeout=10
        )
        data = res.json()

        products = []
        if isinstance(data, list) and data:
            for p in data:
                products.append({
                    "id": p.get("id"),
                    "name": p.get("name", "Product"),
                    "price": p.get("price", "0"),
                    "weight": p.get("weight") or "N/A"
                })
        return products
    except Exception as e:
        print(f"❌ Error fetching WooCommerce products: {e}")
        return []

def get_wc_product_by_id(product_id):
    """Fetch a single product by ID from WooCommerce"""
    try:
        store_url = get_config_value("store_url") 
        if not store_url:
            return None
            
        wc_url = f"{store_url}/wp-json/wc/v3/products/{product_id}"
        consumer_key = get_config_value("consumer_key")
        consumer_secret = get_config_value("consumer_secret")

        res = requests.get(wc_url, auth=(consumer_key, consumer_secret), timeout=10)
        product = res.json()
        
        if isinstance(product, dict) and product.get("id"):
            return {
                "id": product.get("id"),
                "name": product.get("name", "Product"),
                "price": product.get("price", "0"),
                "weight": product.get("weight") or "N/A"
            }
        return None
    except Exception as e:
        print(f"❌ Error fetching WooCommerce product: {e}")
        return None

def get_wc_product_by_name(product_name):
    """Fetch products by name from WooCommerce"""
    try:
        store_url = get_config_value("store_url") 
        if not store_url:
            return None
            
        wc_url = f"{store_url}/wp-json/wc/v3/products"
        consumer_key = get_config_value("consumer_key")
        consumer_secret = get_config_value("consumer_secret")

        res = requests.get(
            wc_url,
            params={"search": product_name, "per_page": 5},
            auth=(consumer_key, consumer_secret),
            timeout=10
        )
        data = res.json()

        if isinstance(data, list) and data:
            # Return the first matching product
            product = data[0]
            return {
                "id": product.get("id"),
                "name": product.get("name", "Product"),
                "price": product.get("price", "0"),
                "weight": product.get("weight") or "N/A"
            }
        return None
    except Exception as e:
        print(f"❌ Error searching WooCommerce products: {e}")
        return None

# ---------------------------
# Tracking Functions
# ---------------------------
def detect_courier(tracking_number):
    tracking_number = tracking_number.upper()

    # ST Courier: 10–12 digits or starts with ST
    if tracking_number.startswith("ST") or (tracking_number.isdigit() and len(tracking_number) in [10, 11, 12]):
        return "ST_COURIER"

    # India Post: 13 chars, usually ends with IN
    elif len(tracking_number) == 13 and tracking_number.endswith("IN"):
        return "INDIA_POST"

    # BlueDart: 8–9 digits
    elif tracking_number.isdigit() and len(tracking_number) in [8, 9]:
        return "BLUEDART"

    # Delhivery: Alphanumeric 10–14 chars, not pure digits
    elif not tracking_number.isdigit() and len(tracking_number) in range(10, 15):
        return "DELHIVERY"

    return "UNKNOWN"

def get_tracking_status(tracking_number, from_number):
    """Get tracking status for a provided tracking number"""
    try:
        courier = detect_courier(tracking_number)

        if courier == "ST_COURIER":
            url = "https://stcourier.com/track/doCheck"
            payload = {"awb_no": tracking_number}
            response = requests.post(url, data=payload, timeout=10)
            data = response.json()

            if data.get("code") == 200:
                status = data.get("msg", "Unknown")
                reply = (
                    f"📦 Tracking Number: {tracking_number}\n"
                    f"📊 Status: {status}\n"
                    f"📍 Location: N/A\n"
                    f"🗓️ Date: N/A"
                )
            else:
                reply = f"⚠️ Tracking number {tracking_number} not found in ST Courier."

        elif courier == "INDIA_POST":
            reply = (
                f"📦 Tracking Number: {tracking_number}\n"
                "🔗 Track online: "
                f"https://www.indiapost.gov.in/_layouts/15/DOP.Portal.Tracking/TrackConsignment.aspx?{tracking_number}"
            )

        elif courier == "BLUEDART":
            reply = (
                f"📦 Tracking Number: {tracking_number}\n"
                f"🔗 Track online: https://www.bluedart.com/tracking/{tracking_number}"
            )

        elif courier == "DELHIVERY":
            reply = (
                f"📦 Tracking Number: {tracking_number}\n"
                f"🔗 Track online: https://www.delhivery.com/track/{tracking_number}"
            )

        else:
            reply = (
                f"⚠️ Sorry, I couldn't detect the courier for tracking number {tracking_number}.\n"
                "👉 Please check your number or contact support."
            )

        # Log request
        log_tracking_request(from_number, tracking_number, courier, "queried")
        return reply

    except Exception as e:
        print(f"Error in tracking lookup: {e}")
        return "⚠️ Sorry, I couldn't retrieve tracking information. Please try again later or contact support."

def log_tracking_request(phone_number, tracking_number, courier, status):
    """Log tracking requests for analytics"""
    connection = None
    try:
        connection = get_db_connection()
        if not connection:
            return
            
        with connection.cursor() as cursor:
            cursor.execute("""
                INSERT INTO tracking_requests 
                (phone_number, tracking_number, courier, status, requested_at)
                VALUES (%s, %s, %s, %s, NOW())
            """, (phone_number, tracking_number, courier, status))
            connection.commit()
    except Exception as e:
        print(f"Error logging tracking request: {e}")
    finally:
        if connection:
            connection.close()

# ---------------------------
# Payment Link Creation
# ---------------------------
def create_payment_link_and_confirm_order(from_number):
    """Generate payment link after delivery details and return confirmation message"""
    try:
        # Get user data from cache
        address_info = get_user_cache(from_number, "address_info")
        order_data = get_user_cache(from_number, "order_data") or {}
        order_type = order_data.get("order_type", "single")

        print(f"🔍 create_payment_link called for: {from_number}")
        print(f"📦 Order data: {json.dumps(order_data, indent=2, default=str)}")
        print(f"🏠 Address info: {json.dumps(address_info, indent=2, default=str)}")

        if not address_info:
            return "⚠️ Error: Delivery information not found. Please fill out the form again."

        # Extract and validate fields
        customer_name = str(address_info.get("full_name", "")).strip()
        customer_contact_raw = str(address_info.get("mobile_number", ""))
        shipping_address = str(address_info.get("shipping_address", "")).strip()

        customer_contact = re.sub(r'\D', '', customer_contact_raw)

        validation_errors = []
        if not customer_name or len(customer_name) < 2:
            validation_errors.append("Full name must be at least 2 characters")
        if not customer_contact or len(customer_contact) != 10 or not customer_contact.isdigit():
            validation_errors.append("Mobile number must be a valid 10-digit number")
        if not shipping_address or len(shipping_address) < 10:
            validation_errors.append("Shipping address must be at least 10 characters")

        if validation_errors:
            return "⚠️ Please fix the following errors:\n• " + "\n• ".join(validation_errors)

        # Extract order details
        if order_type == "cart":
            total_amount_rupees = order_data.get("total_amount_rupees", 0)
            summary_lines = order_data.get("summary_lines", [])
            order_summary = "\n".join(summary_lines)
        else:
            product = order_data.get("selected_product", {})
            quantity = order_data.get("quantity", 1)
            total_amount_rupees = order_data.get("total_price", 0)
            name = product.get("name", "Product")
            order_summary = f"{name} x {quantity} - Rs.{total_amount_rupees:.2f}"

        if total_amount_rupees <= 0 or total_amount_rupees > 1000000:
            return "⚠️ Error: Invalid order amount. Please try ordering again."

        # Optional email
        customer_email = str(address_info.get("email", "")).strip()
        if customer_email and not re.match(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$', customer_email):
            customer_email = ""

        # 🔑 Prepare Razorpay-safe payload
        payment_data = {
            "amount": int(total_amount_rupees * 100),
            "currency": "INR",
            "description": "Cake Shop Order",
            "customer": {
                "name": sanitize_for_razorpay(customer_name, 45),
                "contact": f"+91{customer_contact}"
            },
            "notes": {
                "from_number": sanitize_for_razorpay(from_number),
                "order_summary": sanitize_for_razorpay(order_summary),
                "shipping_address": sanitize_for_razorpay(shipping_address, 500),
            }
        }

        if customer_email:
            payment_data["customer"]["email"] = sanitize_for_razorpay(customer_email, 45)

        print("🚨 Payment payload sent to Razorpay:", json.dumps(payment_data, indent=2))

        # Create payment link
        try:
            # Reinitialize razorpay client in case credentials changed
            initialize_configuration()
            
            if not razorpay_client:
                return "⚠️ Error: Payment service not configured. Please contact support."
                
            payment = razorpay_client.payment_link.create(payment_data)
            print(f"✅ Razorpay response received: {payment.get('id')}")

            payment_link = payment.get("short_url")
            payment_link_id = payment.get("id")

            if not payment_link or not payment_link_id:
                return "⚠️ Error: Could not create payment link. Please try again."

            # Save transaction
            transaction_data = {
                "user": from_number,
                "status": "pending",
                "payment_link_id": payment_link_id,
                "order_id": payment.get("order_id") or f"temp_{payment_link_id}",  
                "total_amount": total_amount_rupees,
                "order_summary": order_summary,
                "address_info": address_info,
                "customer_info": {
                    "name": customer_name,
                    "email": customer_email,
                    "phone": customer_contact
                }
            }
            result = save_transaction(transaction_data)
            print(f"💾 Transaction saved with ID: {result}")

            confirmation_msg = (
                f"🛒 Order Summary:\n{order_summary}\n\n"
                f"💰 Total: Rs.{total_amount_rupees:.2f}\n\n"
                f"✅ Please confirm your order by completing payment:\n{payment_link}\n\n"
                f"📦 Delivery to: {customer_name}, {customer_contact}\n"
                f"📍 Address: {shipping_address}\n\n"
                f"Email: {customer_email or 'N/A'}\n\n"
                f"Courier_type: {address_info.get('courier_type', 'N/A')}\n"
            )
            return confirmation_msg

        except razorpay.errors.BadRequestError as e:
            print(f"❌ Razorpay Bad Request Error: {e}")
            error_msg = str(e).lower()
            if "amount" in error_msg:
                return "⚠️ Error: Invalid amount specified. Please try ordering again."
            elif "customer" in error_msg or "contact" in error_msg:
                return "⚠️ Error: Invalid customer information. Please check your name and phone number."
            elif "email" in error_msg:
                return "⚠️ Error: Invalid email format. Please provide a valid email or leave it blank."
            else:
                return f"⚠️ Error: Payment request failed. {str(e)}"

    except Exception as e:
        print(f"❌ Unexpected error creating payment link: {e}")
        import traceback
        traceback.print_exc()
        return f"⚠️ Sorry, there was an unexpected error: {str(e)}"

# ---------------------------
# Routes
# ---------------------------
@app.route("/webhook", methods=["GET", "POST"])
def whatsapp_webhook():
    # Reinitialize config in case values changed
    initialize_configuration()
    
    if request.method == "GET":
        try:
            params = request.args.to_dict()
            nodejs_url = "https://fixzo.in/webhook"
            response = requests.get(nodejs_url, params=params, timeout=5)
            return response.text, response.status_code
        except Exception as e:
            print(f"Verification forward error: {e}")
            return "ok", 200

    elif request.method == "POST":
        data = request.get_json()
        if not data:
            return "No data", 400

        try:
            entry = data.get("entry", [])[0]
            change = entry.get("changes", [])[0]
            messages = change.get("value", {}).get("messages", [])

            for message in messages:
                from_number = message.get("from", "unknown")
                message_type = message.get("type")

                # ---- FLOW RESPONSE (delivery form) ----
                if message_type == "interactive":
                    interactive = message.get("interactive", {})
                    interactive_type = interactive.get("type")

                    if interactive_type in ["flow_response", "nfm_reply"]:
                        print(f"🎯 FLOW RESPONSE DETECTED ({interactive_type})")
                        print(f"📦 Raw interactive data: {json.dumps(interactive, indent=2)}")

                        response_data = {}
                        if interactive_type == "flow_response":
                            flow_response = interactive.get("flow_response", {})
                            response_data = flow_response.get("response", {}).get("data", {})
                            print(f"📋 Flow response data: {json.dumps(response_data, indent=2)}")
                            
                        elif interactive_type == "nfm_reply":
                            nfm_reply = interactive.get("nfm_reply", {})
                            response_json = nfm_reply.get("response_json")
                            print(f"📋 NFM reply raw JSON: {response_json}")
                            
                            try:
                                if response_json:
                                    # First, parse the outer JSON
                                    parsed_response = json.loads(response_json)
                                    print(f"📋 Parsed NFM data: {json.dumps(parsed_response, indent=2)}")
                                    
                                    # Extract the actual form data
                                    response_data = {
                                        "full_name": parsed_response.get("full_name", ""),
                                        "mobile_number": parsed_response.get("mobile_number", ""),
                                        "shipping_address": parsed_response.get("shipping_address", ""),
                                        "email": parsed_response.get("email", ""),
                                        "courier_type": parsed_response.get("courier_type", ""),
                                    }
                                    
                                    # Clean up the data
                                    for key in response_data:
                                        if isinstance(response_data[key], str):
                                            response_data[key] = response_data[key].strip()
                                    
                                    print(f"🧹 Cleaned response data: {json.dumps(response_data, indent=2)}")
                                else:
                                    response_data = {}
                            except Exception as e:
                                print(f"❌ Error parsing nfm_reply JSON: {e}")
                                import traceback
                                traceback.print_exc()
                                response_data = {}

                        # Validate the extracted data
                        print(f"🔍 Validating response data: {json.dumps(response_data, indent=2)}")
                        
                        # Check if we have the required fields
                        required_fields = ['full_name', 'mobile_number', 'shipping_address']
                        missing_fields = []
                        
                        for field in required_fields:
                            field_value = response_data.get(field, "")
                            if not field_value or not str(field_value).strip():
                                missing_fields.append(field)
                        
                        if missing_fields:
                            print(f"❌ Missing required fields: {missing_fields}")
                            error_msg = f"❌ Please provide all required information. Missing: {', '.join(missing_fields)}"
                            send_whatsapp_message(from_number, error_msg)
                            return "ok", 200

                        if response_data:
                            # Store in cache using MySQL
                            set_user_cache(from_number, "address_info", response_data)
                            
                            # Create payment link
                            confirmation_message = create_payment_link_and_confirm_order(from_number)
                            send_whatsapp_message(from_number, confirmation_message)
                        else:
                            error_msg = "❌ No valid delivery information received. Please try filling the form again."
                            send_whatsapp_message(from_number, error_msg)

                        return "ok", 200

                    # ---- TEXT MESSAGE ----
                    elif message_type == "text":
                        text_body = message["text"]["body"].strip().lower()
                        print(f"📄 Text: {text_body}")

                        # Skip WhatsApp system texts after flow
                        skip_texts = ["response sent", "enter delivery details", "delivery details submitted"]
                        if text_body in skip_texts:
                            print("ℹ️ Skipping system message from flow submission")
                            return "ok", 200

                        # Forward user texts to Node.js
                        try:
                            nodejs_url = "https://fixzo.in/webhook"
                            headers = {"Content-Type": "application/json"}
                            response = requests.post(nodejs_url, json=data, headers=headers, timeout=5)
                            print(f"   🔄 Forwarded to Node.js - Status: {response.status_code}")
                            return response.text, response.status_code
                        except Exception as e:
                            print(f"   ❌ Error forwarding to Node.js: {e}")
                            return "ok", 200

            # Forward other messages to Node.js
            try:
                nodejs_url = "https://fixzo.in/webhook"
                headers = {"Content-Type": "application/json"}
                response = requests.post(nodejs_url, json=data, headers=headers, timeout=5)
                print(f"   🔄 Forwarded to Node.js - Status: {response.status_code}")
                return response.text, response.status_code
            except Exception as e:
                print(f"   ❌ Error forwarding to Node.js: {e}")
                return "ok", 200

        except Exception as e:
            import traceback
            print(f"❌ Webhook error: {e}")
            traceback.print_exc()
            return "ok", 200

@app.route("/ai-reply", methods=["POST"])
def ai_reply():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"reply": "Invalid request data"})
            
        from_number = data.get("from")
        user_message = data.get("userMessage", "").lower().strip()
        order = data.get("order")

        if not from_number:
            return jsonify({"reply": "Missing from number"})

        reply = "How can I assist you today?"

        # -------------------------------
        # 1. CART CHECKOUT (order_selected)
        # -------------------------------
        if user_message == "order_selected" and order:
            try:
                product_items = order.get("product_items", [])
                total_amount = 0
                summary_lines = []

                for item in product_items:
                    product_id = item.get("product_retailer_id")
                    quantity = int(item.get("quantity", 1))
                    
                    price_value = int(item.get("item_price", 0))  # WhatsApp gives in paise
                    item_total = price_value * quantity
                    total_amount += item_total

                    # Try product name from cache
                    product_name = f"Product {product_id}"
                    products_data = get_user_cache(from_number, "products")
                    if products_data:
                        user_products = products_data.get("products", [])
                        product_obj = next((p for p in user_products if p.get("retailer_id") == product_id), None)
                        if product_obj:
                            product_name = product_obj.get("name", product_name)
                    
                    summary_lines.append(f"🛍 {product_name} x {quantity} → ₹{item_total:.2f}")

                # Store order details in cache
                order_data = {
                    "order_type": "cart",
                    "product_items": product_items,
                    "total_amount": total_amount * 100,   # paise
                    "total_amount_rupees": total_amount,  # rupees
                    "summary_lines": summary_lines
                }
                set_user_cache(from_number, "order_data", order_data)

                # Send cart summary
                cart_summary = (
                    "🛒 Cart Summary:\n" +
                    "\n".join(summary_lines) +
                    f"\n\n💰 Total: ₹{total_amount:.2f}\n\n" +
                    "📋 I'm sending you a delivery form. Please fill it out to continue."
                )
                send_whatsapp_message(from_number, cart_summary)

                # Send flow form
                flow_data = {"order_summary": "\n".join(summary_lines), "total_amount": total_amount}
                flow_response = send_delivery_flow(from_number, flow_data)

                if flow_response and flow_response.get("error"):
                    reply = (
                        "📋 Please provide your delivery information:\n\n"
                        "• Full Name:\n"
                        "• Mobile Number:\n"
                        "• Shipping Address:\n"
                        "• Email:\n"
                        "• Courier Type (ST Courier or India Post):\n"
                        "• Preferred Delivery Date:\n\n"
                        "Example:\n"
                        "John Doe\n9876543210\n123 Main St, City, State, PIN\n"
                        "john@example.com\nST Courier\n2025-12-25"
                    )
                else:
                    reply = "I've sent you a delivery form. Please fill it out to continue."
            except Exception as e:
                reply = f"⚠️ Error processing order: {str(e)}"

        # -------------------------------
        # 2. CHECK PAYMENT
        # -------------------------------
        elif "check payment" in user_message:
            try:
                connection = get_db_connection()
                if not connection:
                    return jsonify({"reply": "⚠️ Database connection error"})
                    
                with connection.cursor() as cursor:
                    cursor.execute(
                        "SELECT * FROM transactions WHERE user = %s ORDER BY created_at DESC LIMIT 1",
                        (from_number,)
                    )
                    last_txn = cursor.fetchone()
                    
                if last_txn:
                    status = last_txn.get("status", "pending")
                    payment_link_id = last_txn.get("payment_link_id")
                    payment_id = last_txn.get("payment_id")

                    # Re-check pending
                    if status == "pending" and (payment_link_id or payment_id):
                        if payment_link_id:
                            try:
                                pl_data = razorpay_client.payment_link.fetch(payment_link_id)
                                payments = pl_data.get("payments", [])
                                if payments:
                                    latest_payment = payments[0]
                                    new_status = latest_payment.get("status", "pending")
                                    if new_status != status:
                                        status = new_status
                                        update_transaction_status(last_txn["id"], status, latest_payment.get("id"))
                            except:
                                pass
                        elif payment_id:
                            new_status = check_payment_status(payment_id)
                            if new_status not in ["error", "invalid_id"] and new_status != status:
                                status = new_status
                                update_transaction_status(last_txn["id"], status)

                    status_display = {
                        "pending": "pending ⏳",
                        "captured": "completed ✅", 
                        "failed": "failed ❌",
                        "authorized": "authorized ⚡",
                        "refunded": "refunded ↩️",
                    }.get(status, f"{status} ✅")

                    reply = (
                        f"🧾 Last Transaction:\n"
                        f"Order ID: {last_txn.get('payment_link_id') or last_txn.get('id')}\n"
                        f"Amount: ₹{last_txn.get('total_amount', 0):.2f}\n"
                        f"Status: {status_display}"
                    )
                    if last_txn.get('payment_id'):
                        reply += f"\nPayment ID: {last_txn['payment_id']}"
                else:
                    reply = "⚠️ No past transactions found."
            except Exception as e:
                reply = f"⚠️ Error checking payment: {str(e)}"

        # 3. Show all transactions
        elif any(cmd in user_message for cmd in ["all transaction", "my transaction", "list transaction", "history", "transactions"]):
            txns = get_user_transactions(from_number, 20)
            if txns:
                lines = []
                for i, t in enumerate(txns, 1):
                    status = t.get('status', 'pending')
                    payment_link_id = t.get('payment_link_id')
                    payment_id = t.get('payment_id')
                    
                    # Check payment status in real-time for pending transactions
                    if status == "pending" and (payment_link_id or payment_id):
                        try:
                            if payment_link_id:
                                # Check payment link status
                                payment_link_data = razorpay_client.payment_link.fetch(payment_link_id)
                                payments = payment_link_data.get("payments", [])
                                if payments:
                                    latest_payment = payments[0]
                                    new_status = latest_payment.get("status", "pending")
                                    if new_status != status:
                                        status = new_status
                                        # Update the transaction in database
                                        update_transaction_status(t["id"], status)
                            elif payment_id:
                                # Check individual payment status
                                new_status = check_payment_status(payment_id)
                                if new_status not in ["error", "invalid_id"] and new_status != status:
                                    status = new_status
                                    update_transaction_status(t["id"], status)
                        except Exception as e:
                            print(f"Error checking payment status for transaction {t['id']}: {e}")
                            # Keep the original status if there's an error
                    
                    status_display = {
                        "pending": "⏳ pending",
                        "captured": "✅ completed", 
                        "failed": "❌ failed",
                        "authorized": "⚡ authorized",
                        "refunded": "↩️ refunded",
                        "error": "❗ error",
                    }.get(status, f"✅ {status}")
                    
                    lines.append(
                        f"{i}. Order {t.get('payment_link_id') or t.get('id')} → ₹{t.get('total_amount', 0):.2f} ({status_display})"
                    )
                reply = "📜 Your Transactions:\n" + "\n".join(lines)
            else:
                reply = "⚠️ No transactions found."

        # 4. SHOW CATEGORIES (WooCommerce API, no cache)
        elif user_message == "order":
            try:
                categories = get_wc_categories()
                if categories:
                    # Format categories for display
                    lines = [f"{c['id']}. {c['name']}" for c in categories]
                    reply = "📂 Product Categories:\n" + "\n".join(lines) + (
                        "\n\n👉 Reply with *categoryID* or *category name* to see products."
                    )
                else:
                    reply = "⚠️ No categories found. Please try again later."
            except Exception as e:
                reply = f"⚠️ Error fetching categories: {str(e)}"

        # 5. CATEGORY SELECTED (ID or Name)
        elif user_message.isdigit() or user_message.isalpha():
            try:
                categories = get_wc_categories()
                selected_cat = None

                if user_message.isdigit():
                    selected_cat = next((c for c in categories if str(c["id"]) == user_message), None)
                else:
                    selected_cat = next((c for c in categories if c["name"].lower() == user_message.lower()), None)

                if selected_cat:
                    products = get_wc_products(selected_cat["id"])
                    if products:
                        lines = []
                        for p in products[:50]:  # Limit to 50 products
                            product_id = p.get("id")
                            name = p.get("name", "Product")
                            price = p.get("price", "0")
                            weight = p.get("weight", "N/A")

                            lines.append(f"{product_id}. {name} – ₹{price} – {weight}")

                        reply = f"🛍 Products in {selected_cat['name']}:\n" + "\n".join(lines) + (
                            "\n\n👉 To order, reply with *productID*quantity*weight"
                        )
                    else:
                        reply = f"⚠️ No products found in {selected_cat['name']}."
                else:
                    reply = "⚠️ Invalid category. Please reply with a valid category ID or name."
            except Exception as e:
                reply = f"⚠️ Error fetching products: {str(e)}"

        # 6. PRODUCT SELECTED (ID*qty*weight or Name*qty*weight)
        elif "*" in user_message:
            try:
                parts = user_message.split("*")
                if len(parts) >= 2:
                    prod_identifier = parts[0].strip()
                    quantity = int(parts[1])
                    weight = parts[2].strip() if len(parts) > 2 else "N/A"

                    # Fetch product details
                    product = None
                    if prod_identifier.isdigit():
                        product = get_wc_product_by_id(int(prod_identifier))
                    else:
                        product = get_wc_product_by_name(prod_identifier)

                    if product:
                        total_price = float(product.get("price", 0)) * quantity
                        order_data = {
                            "order_type": "single",
                            "selected_product": product,
                            "quantity": quantity,
                            "weight": weight,
                            "total_price": total_price
                        }
                        set_user_cache(from_number, "order_data", order_data)

                        reply = f"✅ You selected *{product['name']}* x{quantity} ({weight}) – ₹{total_price:.2f}\n\n📋 I'm sending you a delivery form."
                        
                        # Send flow form
                        flow_data = {
                            "order_summary": f"{product['name']} x {quantity} ({weight}) → ₹{total_price:.2f}",
                            "total_amount": total_price
                        }
                        flow_response = send_delivery_flow(from_number, flow_data)

                        if flow_response and flow_response.get("error"):
                            reply += "\n\n📋 Please provide your delivery information:\n\n• Full Name:\n• Mobile Number:\n• Shipping Address:\n• Email:\n• Courier Type (ST Courier or India Post):\n• Preferred Delivery Date:"
                    else:
                        reply = "⚠️ Product not found. Please type 'order' again."
                else:
                    reply = "⚠️ Invalid format. Use *productID*quantity*weight OR *productName*quantity*weight"
            except Exception as e:
                reply = f"⚠️ Error processing selection: {str(e)}"

        # 7. TRACKING REQUEST
        elif "track" in user_message or "tracking" in user_message:
            # Extract tracking number from message
            tracking_match = re.search(r'\b([A-Z0-9]{8,15})\b', user_message.upper())
            if tracking_match:
                tracking_number = tracking_match.group(1)
                reply = get_tracking_status(tracking_number, from_number)
            else:
                reply = "📦 Please provide your tracking number. Example: 'track 1234567890'"

        # 8. DEFAULT FALLBACK
        else:
            reply = """👋 Hello! Welcome to our shop 🛍️  
✨ Discover our products and great deals.  
🛒 Type "order" to view our product categories.  
💳 Type "check payment" to see your latest payment status.  
📜 Type "all transactions" to view your order history.  
📦 Type "track [number]" to check your delivery status."""

        return jsonify({"reply": reply})
                
    except Exception as e:
        print(f"Error in ai-reply: {e}")
        return jsonify({"reply": "⚠️ Sorry, I encountered an error processing your request."})

@app.route("/payment-webhook", methods=["POST"])
def payment_webhook():
    try:
        raw_data = request.get_data(as_text=True)
        print(f"=== RAW WEBHOOK DATA ===")
        print(raw_data)
        print("========================")
        
        data = request.get_json()
        if not data:
            print("❌ No JSON data received in webhook")
            return jsonify({"error": "No data received"}), 400
            
        print(f"=== RAZORPAY WEBHOOK RECEIVED ===")
        print(f"Webhook data: {json.dumps(data, indent=2)}")
        
        if data.get("event") == "ping":
            print("✅ Received ping test webhook")
            return jsonify({"success": True, "message": "Pong"}), 200
        
        payment_entity = None
        
        if data.get("payload", {}).get("payment", {}).get("entity"):
            payment_entity = data["payload"]["payment"]["entity"]
            print("Found payment data in: payload.payment.entity")
        
        elif data.get("payload", {}).get("entity"):
            payment_entity = data["payload"]["entity"]
            print("Found payment data in: payload.entity")
        
        elif data.get("entity"):
            payment_entity = data["entity"]
            print("Found payment data in: entity")
        
        if not payment_entity:
            print("❌ Could not find payment entity in webhook data")
            return jsonify({"error": "No payment entity found"}), 400
        
        payment_id = payment_entity.get("id")
        status = payment_entity.get("status")
        order_id = payment_entity.get("order_id")
        notes = payment_entity.get("notes", {})
        
        print(f"Payment ID: {payment_id}, Status: {status}, Order ID: {order_id}")
        print(f"Notes: {notes}")
        
        if not payment_id or not status:
            print("❌ Missing payment_id or status in webhook")
            return jsonify({"error": "Missing payment_id or status"}), 400
        
        transaction = None
        
        # Try to find transaction by various identifiers
        if notes and isinstance(notes, dict):
            payment_link_id = notes.get("payment_link_id")
            if payment_link_id:
                transaction = get_transaction_by_field("payment_link_id", payment_link_id)
        
        if not transaction and payment_id:
            transaction = get_transaction_by_field("payment_id", payment_id)
        
        if not transaction and order_id:
            transaction = get_transaction_by_field("order_id", order_id)
        
        if not transaction and notes and isinstance(notes, dict) and notes.get("from_number"):
            from_number = notes.get("from_number")
            transaction = get_transaction_by_field("user", from_number)
        
        if transaction:
            status_mapping = {
                "captured": "completed",
                "authorized": "authorized",
                "failed": "failed",
                "pending": "pending",
                "refunded": "refunded"
            }

            new_status = status_mapping.get(status, status)
            old_status = transaction.get("status")

            # Only process if status actually changed
            if old_status != new_status:
                update_data = {
                    "status": new_status,
                    "payment_id": payment_id,
                }

                if status == "captured":
                    update_data["paid_at"] = datetime.utcnow()

                # Update DB
                update_transaction_status(transaction["id"], new_status, payment_id)

                print(f"✅ Updated transaction {transaction['id']} from {old_status} to {new_status}")

                # Send WhatsApp message once
                if new_status == "completed":
                    try:
                        message = (
                            f"✅ Payment successful! Your order is confirmed.\n"
                            f"Payment ID: {payment_id}\n"
                            f"Amount: ₹{transaction.get('total_amount', 0):.2f}"
                        )
                        send_whatsapp_message(transaction["user"], message)
                    except Exception as e:
                        print(f"Error sending confirmation message: {e}")

                elif new_status == "failed":
                    send_whatsapp_message(
                        transaction["user"],
                        f"❌ Payment failed for Payment ID: {payment_id}. Please try again."
                    )
            else:
                print(f"⚠️ Duplicate webhook ignored for transaction {transaction['id']} (status: {old_status})")

            return jsonify({"success": True, "message": "Transaction processed"}), 200

        else:
            print("❌ Could not find matching transaction")
            # Create a new transaction record
            from_number = notes.get("from_number", "unknown") if isinstance(notes, dict) else "unknown"
            
            transaction_data = {
                "user": from_number,
                "status": "completed" if status == "captured" else status,
                "payment_id": payment_id,
                "order_id": order_id,
                "total_amount": payment_entity.get("amount", 0) / 100,
                "order_summary": "Payment received via webhook",
                "address_info": {},
                "customer_info": {}
            }
            
            save_transaction(transaction_data)
            print(f"✅ Created new transaction for payment_id: {payment_id}")
            return jsonify({"success": True, "message": "New transaction created"}), 200
            
    except Exception as e:
        print(f"❌ Error in payment webhook: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({"error": "Internal server error"}), 500

@app.route("/health", methods=["GET"])
def health():
    try:
        connection = get_db_connection()
        if not connection:
            return jsonify({"status": "unhealthy", "database": "error"})
            
        with connection.cursor() as cursor:
            cursor.execute("SELECT 1")
        db_status = "connected"
        
        # Check if configuration is loaded
        if not razorpay_client or not ACCESS_TOKEN:
            config_status = "incomplete"
        else:
            config_status = "complete"
            
        return jsonify({
            "status": "healthy", 
            "database": db_status,
            "configuration": config_status
        })
    except Exception as e:
        print(f"Database health check failed: {e}")
        return jsonify({"status": "unhealthy", "database": "error"})

# Debug endpoint to test flow sending
@app.route("/test-flow", methods=["GET"])
def test_flow():
    """Test endpoint to debug flow sending"""
    to_number = request.args.get("to")
    if not to_number:
        return jsonify({"error": "Missing 'to' parameter"}), 400
    
    flow_data = {
        "order_summary": "Test Product x 1 → ₹100.00",
        "total_amount": 100
    }
    
    result = send_delivery_flow(to_number, flow_data)
    return jsonify(result)

if __name__ == "__main__":
    # Initialize database and configuration
   
    initialize_configuration()
    
    # Start background threads
    payment_check_thread = threading.Thread(target=check_pending_payments)
    payment_check_thread.daemon = True
    payment_check_thread.start()
    
    cleanup_thread = threading.Thread(target=clean_old_cache)
    cleanup_thread.daemon = True
    cleanup_thread.start()
    
    app.run(host='0.0.0.0', port=3021, debug=True, ssl_context=('ssl/certificate.crt', 'ssl/private.key'))