import React, { useState } from 'react';

const Form: React.FC = () => {
  const [email, setEmail] = useState('');
  const [firstName, setFirstName] = useState('');
  const [phone, setPhone] = useState('');
  const [message, setMessage] = useState('');
  const [status, setStatus] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const contactData = { name: firstName, email, message, phone };

    const response = await fetch('/api/submit-contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(contactData),
    });

    if (response.ok) {
      setStatus('Message sent successfully!');
      setEmail('');
      setFirstName('');
      setPhone('');
      setMessage('');
    } else {
      setStatus('Failed to send message.');
    }
  };

  return (
    <div className="bg-white py-12 px-4">
      <form className="max-w-7xl mx-auto" onSubmit={handleSubmit}>
        <div className="grid md:grid-cols-6 md:gap-8">
          <div className="relative z-0 w-full bg-[#F4F4F4] rounded-lg mb-5 col-span-2 group">
            <input
              type="email"
              name="floating_email"
              id="floating_email"
              className="block py-3.5 px-0 w-full h-[69px] text-xl text-gray-950 bg-transparent appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
              placeholder=" "
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <label
              htmlFor="floating_email"
              className="peer-focus:font-medium absolute px-2 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
            >
              Email
            </label>
          </div>

          <div className="relative z-0 w-full bg-[#F4F4F4] rounded-lg mb-5 col-span-2 group">
            <input
              type="text"
              name="floating_first_name"
              id="floating_first_name"
              className="block py-3.5 px-0 w-full h-[69px] text-xl text-gray-950 bg-transparent appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
              placeholder=" "
              required
              value={firstName}
              onChange={(e) => setFirstName(e.target.value)}
            />
            <label
              htmlFor="floating_first_name"
              className="peer-focus:font-medium absolute px-2 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
            >
              First name
            </label>
          </div>

          <div className="relative z-0 w-full bg-[#F4F4F4] rounded-lg mb-5 col-span-2 group">
            <input
              type="tel"
              name="floating_phone"
              id="floating_phone"
              className="block py-3.5 px-0 w-full h-[69px] text-xl text-gray-950 bg-transparent appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
              placeholder=" "
              required
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
            />
            <label
              htmlFor="floating_phone"
              className="peer-focus:font-medium absolute px-2 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
            >
              Phone
            </label>
          </div>
        </div>

        <div className="relative z-0 w-full bg-[#F4F4F4] rounded-lg mb-5 group">
          <textarea
            name="floating_message"
            id="floating_message"
            rows={4}
            className="block py-6 w-full text-sm text-gray-900 bg-transparent appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
            placeholder=""
            value={message}
            onChange={(e) => setMessage(e.target.value)}
          ></textarea>
          <label
            htmlFor="floating_message"
            className="peer-focus:font-medium absolute px-2 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
          >
            Message
          </label>
        </div>

        <button
          type="submit"
          className="text-white bg-red-400 p-6 rounded text-center"
        >
          SUBMIT
        </button>

        {status && <p className="mt-4 text-lg">{status}</p>}
      </form>
    </div>
  );
};

export default Form;
