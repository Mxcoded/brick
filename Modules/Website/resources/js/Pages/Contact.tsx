import { useState } from 'react';
import { MapPin, Phone, Mail, Clock, Send, MessageSquare } from 'lucide-react';
import { Link } from '@inertiajs/react';
import {
  FaFacebook,
  FaTwitter,
  FaLinkedin,
  FaInstagram,
} from "react-icons/fa";

const Contact = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
  });
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitted(true);
    setTimeout(() => {
      setFormData({ name: '', email: '', phone: '', subject: '', message: '' });
      setSubmitted(false);
      alert('Message sent successfully! (This is a demo)');
    }, 1000);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const contactInfo = [
    {
      icon: MapPin,
      title: 'Our Location',
      details: ['Kolapo Ishola GRA, Akobo', 'Ibadan, Oyo State, Nigeria'],
    },
    {
      icon: Phone,
      title: 'Phone Numbers',
      details: ['+234 800 000 0000', '+234 810 000 0000'],
    },
    {
      icon: Mail,
      title: 'Email Address',
      details: ['info@thebridgehotel.com.ng', 'reservations@thebridgehotel.com.ng'],
    },
    {
      icon: Clock,
      title: 'Working Hours',
      details: ['Reception: 24/7', 'Restaurant: 7:00 AM - 11:00 PM'],
    },
  ];

  const faqItems = [
    {
      question: 'What time is check-in and check-out?',
      answer: 'Check-in is at 2:00 PM and check-out is at 12:00 PM noon. Late check-out can be arranged at an additional charge.',
    },
    {
      question: 'Is breakfast included in the room rate?',
      answer: 'Yes, all room rates include breakfast for 1 guest. Suite rates include breakfast for 2 guests, served in our restaurant.',
    },
    {
      question: 'Do you offer airport transfers?',
      answer: 'Yes, we can arrange airport transfers upon request. Please contact us at least 24 hours in advance to arrange pickup.',
    },
    {
      question: 'Can I book the event hall for weddings or conferences?',
      answer: 'Absolutely! We have event halls and meeting rooms available for all occasions. Contact our events team for more information.',
    },
    {
      question: 'What payment methods do you accept?',
      answer: 'We accept cash, bank transfers, and major debit/credit cards including Visa and Mastercard.',
    },
  ];

  return (
    <div className="overflow-hidden">
      {/* Hero Section */}
      <section className="relative pt-32 pb-24 lg:pt-40 lg:pb-32">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Hotel"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-primary-950/95 to-primary-950/70" />
        </div>
        <div className="relative z-10 container-custom">
          <div className="max-w-3xl">
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">Get in Touch</span>
            <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mt-3 mb-6">
              Contact Us
            </h1>
            <p className="text-white/90 text-xl leading-relaxed">
              We'd love to hear from you. Whether you have questions, want to make a reservation,
              or need assistance, our team is here to help.
            </p>
          </div>
        </div>
      </section>

      {/* Contact Info Cards */}
      <section className="bg-white py-12 -mt-8 relative z-10">
        <div className="container-custom">
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {contactInfo.map((info, index) => (
              <div
                key={index}
                className="bg-primary-50 rounded-xl p-6 shadow-sm hover:shadow-lg transition-shadow duration-300"
              >
                <div className="w-12 h-12 bg-warm-500/10 rounded-lg flex items-center justify-center mb-4">
                  <info.icon className="w-6 h-6 text-warm-600" />
                </div>
                <h3 className="font-serif text-lg font-semibold text-primary-900 mb-2">
                  {info.title}
                </h3>
                {info.details.map((detail, idx) => (
                  <p key={idx} className="text-primary-600 text-sm">
                    {detail}
                  </p>
                ))}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Contact Form & Map */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="grid lg:grid-cols-2 gap-16">
            {/* Contact Form */}
            <div>
              <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">
                Send Us a Message
              </span>
              <h2 className="font-serif text-3xl md:text-4xl font-bold text-primary-900 mt-3 mb-8">
                We're Here to Help
              </h2>

              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-2">
                      Full Name <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name}
                      onChange={handleChange}
                      required
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all"
                      placeholder="John Doe"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-2">
                      Email Address <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="email"
                      name="email"
                      value={formData.email}
                      onChange={handleChange}
                      required
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all"
                      placeholder="john@example.com"
                    />
                  </div>
                </div>

                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-2">
                      Phone Number
                    </label>
                    <input
                      type="tel"
                      name="phone"
                      value={formData.phone}
                      onChange={handleChange}
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all"
                      placeholder="+234 800 000 0000"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-2">
                      Subject <span className="text-red-500">*</span>
                    </label>
                    <select
                      name="subject"
                      value={formData.subject}
                      onChange={handleChange}
                      required
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all appearance-none"
                    >
                      <option value="">Select a topic</option>
                      <option value="reservation">Room Reservation</option>
                      <option value="restaurant">Restaurant Booking</option>
                      <option value="event">Event Inquiry</option>
                      <option value="feedback">Feedback</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-2">
                    Your Message <span className="text-red-500">*</span>
                  </label>
                  <textarea
                    name="message"
                    value={formData.message}
                    onChange={handleChange}
                    required
                    rows={5}
                    className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all resize-none"
                    placeholder="How can we help you?"
                  />
                </div>

                <button
                  type="submit"
                  disabled={submitted}
                  className="btn-primary inline-flex items-center space-x-2"
                >
                  {submitted ? (
                    <>
                      <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                      <span>Sending...</span>
                    </>
                  ) : (
                    <>
                      <Send className="w-5 h-5" />
                      <span>Send Message</span>
                    </>
                  )}
                </button>
              </form>
            </div>

            {/* Map & Social */}
            <div>
              <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">
                Find Us
              </span>
              <h2 className="font-serif text-3xl md:text-4xl font-bold text-primary-900 mt-3 mb-8">
                Our Location
              </h2>

              {/* Map Placeholder */}
              <div className="bg-primary-100 rounded-xl overflow-hidden aspect-video mb-8 relative">
                <img
                  src="https://images.pexels.com/photos/4602481/pexels-photo-4602481.jpeg?auto=compress&cs=tinysrgb&w=800"
                  alt="Location map"
                  className="w-full h-full object-cover opacity-50"
                />
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="text-center">
                    <MapPin className="w-12 h-12 text-warm-600 mx-auto mb-2" />
                    <p className="text-primary-700 font-medium">The Bridge Hotel</p>
                    <p className="text-primary-600 text-sm">Kolapo Ishola GRA, Akobo, Ibadan</p>
                  </div>
                </div>
              </div>

              {/* Social Media */}
              <div>
                <h4 className="font-medium text-primary-900 mb-4">Follow Us</h4>
                <div className="flex space-x-4">
                  <a
                    href="#"
                    className="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center hover:bg-warm-500 hover:text-white transition-all duration-300 text-primary-600"
                  >
                    <FaFacebook className="w-5 h-5" />
                  </a>
                  <a
                    href="#"
                    className="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center hover:bg-warm-500 hover:text-white transition-all duration-300 text-primary-600"
                  >
                    <FaInstagram className="w-5 h-5" />
                  </a>
                  <a
                    href="#"
                    className="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center hover:bg-warm-500 hover:text-white transition-all duration-300 text-primary-600"
                  >
                    <FaTwitter className="w-5 h-5" />
                  </a>
                  <a
                    href="#"
                    className="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center hover:bg-warm-500 hover:text-white transition-all duration-300 text-primary-600"
                  >
                    <FaLinkedin className="w-5 h-5" />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="bg-primary-50 section-padding">
        <div className="container-custom">
          <div className="text-center mb-16">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">
              Frequently Asked Questions
            </span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              Common Questions
            </h2>
          </div>

          <div className="max-w-3xl mx-auto space-y-4">
            {faqItems.map((item, index) => (
              <div
                key={index}
                className="bg-white rounded-xl p-6 shadow-sm"
              >
                <div className="flex items-start space-x-4">
                  <div className="flex-shrink-0">
                    <MessageSquare className="w-6 h-6 text-warm-500" />
                  </div>
                  <div>
                    <h4 className="font-medium text-primary-900 mb-2">{item.question}</h4>
                    <p className="text-primary-600">{item.answer}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-primary-900 section-padding">
        <div className="container-custom text-center">
          <h2 className="font-serif text-4xl md:text-5xl font-bold text-white mb-6">
            Ready to Book Your Stay?
          </h2>
          <p className="text-primary-200 text-lg max-w-2xl mx-auto mb-10">
            Experience luxury and comfort at The Bridge Hotel. Book your room or restaurant reservation today.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="tel:+2348000000000" className="btn-accent inline-flex items-center space-x-2 text-lg">
              <Phone className="w-5 h-5" />
              <span>Call Us Now</span>
            </a>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Contact;
