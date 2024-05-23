import { NextApiRequest, NextApiResponse } from 'next';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method === 'POST') {
    const { name, email, message, phone } = req.body;

    const apiUrl = 'https://example.com/wp-json/wp-contact-data/v1/submit';
    const username = 'name@email.com'; // Replace with your WordPress username
    const appPassword = 'application password'; // Replace with your application password

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${Buffer.from(`${username}:${appPassword}`).toString('base64')}`,
      },
      body: JSON.stringify({ name, email, message, phone }),
    });

    if (response.ok) {
      return res.status(200).json({ message: 'Message sent successfullys' });
    } else {
      const errorData = await response.json();
      return res.status(response.status).json({ message: errorData.message });
    }
  } else {
    res.setHeader('Allow', ['POST']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}
