import { Link } from '@inertiajs/react';
import { MapPin, Phone, Mail, Clock } from 'lucide-react';

const Footer = () => {
  return (
    <footer className="bg-primary-900 text-primary-100">
      {/* Main Footer */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
          {/* Brand */}
          <div>
            <h3 className="font-serif text-2xl font-bold text-white mb-4">
              <span className="text-warm-500">The</span> Bridge Hotel
            </h3>
            <p className="text-primary-300 text-sm leading-relaxed mb-6">
              Experience the perfect blend of luxury and comfort in the heart of Ibadan.
              Where every stay is extraordinary.
            </p>
            <div className="flex space-x-4">
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-primary-800 flex items-center justify-center hover:bg-warm-600 transition-colors duration-300"
              >
                <Clock className="w-5 h-5" />
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-primary-800 flex items-center justify-center hover:bg-warm-600 transition-colors duration-300"
              >
                <Clock className="w-5 h-5" />
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-primary-800 flex items-center justify-center hover:bg-warm-600 transition-colors duration-300"
              >
                <Clock className="w-5 h-5" />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-serif text-lg font-semibold text-white mb-6">Quick Links</h4>
            <ul className="space-y-3">
              {[
                { name: 'Home', path: '/' },
                { name: 'About Us', path: '/about' },
                { name: 'Gallery', path: '/gallery' },
                { name: 'Rooms & Suites', path: '/frontend/room' },
                { name: 'Restaurant', path: '/restaurant' },
                { name: 'Spa & Wellness', path: '/spa' },
                { name: 'Contact Us', path: '/contact' },
              ].map((link) => (
                <li key={link.path}>
                  <Link
                    href={link.path}
                    className="text-primary-300 text-sm hover:text-warm-500 transition-colors duration-300"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h4 className="font-serif text-lg font-semibold text-white mb-6">Contact Us</h4>
            <ul className="space-y-4">
              <li className="flex items-start space-x-3">
                <MapPin className="w-5 h-5 text-warm-500 mt-0.5 flex-shrink-0" />
                <span className="text-primary-300 text-sm">
                  Kolapo Ishola GRA, Akobo, Ibadan, Oyo State, Nigeria
                </span>
              </li>
              <li className="flex items-center space-x-3">
                <Phone className="w-5 h-5 text-warm-500 flex-shrink-0" />
                <span className="text-primary-300 text-sm">+234 800 000 0000</span>
              </li>
              <li className="flex items-center space-x-3">
                <Mail className="w-5 h-5 text-warm-500 flex-shrink-0" />
                <span className="text-primary-300 text-sm">info@thebridgehotel.com.ng</span>
              </li>
            </ul>
          </div>

          {/* Opening Hours */}
          <div>
            <h4 className="font-serif text-lg font-semibold text-white mb-6">Opening Hours</h4>
            <ul className="space-y-3">
              <li className="flex items-center space-x-3">
                <Clock className="w-5 h-5 text-warm-500 flex-shrink-0" />
                <div>
                  <p className="text-primary-300 text-sm">Reception: 24/7</p>
                </div>
              </li>
              <li className="flex items-center space-x-3">
                <Clock className="w-5 h-5 text-warm-500 flex-shrink-0" />
                <div>
                  <p className="text-primary-300 text-sm">Restaurant: 7am - 11pm</p>
                </div>
              </li>
              <li className="flex items-center space-x-3">
                <Clock className="w-5 h-5 text-warm-500 flex-shrink-0" />
                <div>
                  <p className="text-primary-300 text-sm">Pool: 6am - 9pm</p>
                </div>
              </li>
              <li className="flex items-center space-x-3">
                <Clock className="w-5 h-5 text-warm-500 flex-shrink-0" />
                <div>
                  <p className="text-primary-300 text-sm">Spa: 8am - 8pm</p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      {/* Bottom Bar */}
      <div className="border-t border-primary-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p className="text-primary-400 text-sm">
              {new Date().getFullYear()} The Bridge Hotel. All rights reserved.
            </p>
            <div className="flex space-x-6">
              <a href="#" className="text-primary-400 text-sm hover:text-warm-500 transition-colors">
                Privacy Policy
              </a>
              <a href="#" className="text-primary-400 text-sm hover:text-warm-500 transition-colors">
                Terms of Service
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
