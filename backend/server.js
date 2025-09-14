// server.js

// Import express library
const express = require('express');

// Initialize the app
const app = express();

// Define a simple route to check status
app.get('/', (req, res) => {
    res.status(200).send('Server is up and running!');
});

// Set the server to listen on port 3000
const port = 3020;
app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});
