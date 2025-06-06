// server.js
import express from 'express';
import fetch from 'node-fetch'; // Now using import for node-fetch
import cors from 'cors';

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

app.post('/api/vademecum-proxy', async (req, res) => {
    const targetUrl = 'https://cnpm.msal.gov.ar/api/vademecum';
    const { searchdata } = req.body;

    if (!searchdata) {
        return res.status(400).json({ error: 'searchdata is required in the request body' });
    }

    try {
        const response = await fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ searchdata: searchdata })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error(`Upstream API error: ${response.status} - ${errorText}`);
            return res.status(response.status).send(errorText);
        }

        const data = await response.json();
        res.json(data);
    } catch (error) {
        console.error('Proxy error:', error);
        res.status(500).json({ error: 'Failed to fetch data from the upstream API.' });
    }
});

app.get('/', (req, res) => {
    res.send('Proxy server is running!');
});

app.listen(PORT, () => {
    console.log(`Proxy server listening on port ${PORT}`);
    console.log(`Access it at http://localhost:${PORT}`);
});