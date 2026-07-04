import { useState, useEffect } from 'react';
import { Menu, X, Building2 } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';

const Navbar = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  usePage().props; // Access Inertia page props if needed

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 60);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    setIsOpen(false);
  }, []);

  const navLinks = [
    { name: 'Home', path: '/' },
    { name: 'About Us', path: '/about' },
    { name: 'Gallery', path: '/gallery' },
    { name: 'Rooms', path: '/room' },
    { name: 'Restaurant', path: '/restaurants' },
    { name: 'Spa', path: '/spa' },
    { name: 'Contact', path: '/contact' },
  ];

 const isActive = (path: string) => usePage().props.currentRouteName === path;

  return (
    <nav
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
        scrolled
          ? 'bg-primary-950/97 backdrop-blur-md shadow-lg py-3'
          : 'bg-transparent py-5'
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <Link href="/" className="flex items-center space-x-3 group">
            <div className="relative w-11 h-11 rounded-full border border-warm-500/60 flex items-center justify-center group-hover:border-warm-500 transition-colors">
              <Building2 className="w-5 h-5 text-warm-400" />
            </div>
            <div>
              <div className="font-serif text-sm font-bold text-white leading-tight tracking-widest uppercase">
                The Bridge
              </div>
              <div className="text-[10px] text-warm-400 tracking-[0.2em] uppercase">
                Hotel · Ibadan
              </div>
            </div>
          </Link>

          {/* Desktop Navigation — centered */}
          <div className="hidden lg:flex items-center space-x-8">
            {navLinks.map((link) => (
              <Link
                key={link.path}
                href={link.path}
                className={`text-sm font-medium transition-all duration-300 relative group ${
                  isActive(link.path)
                    ? 'text-white'
                    : 'text-white/70 hover:text-white'
                }`}
              >
                {link.name}
                <span
                  className={`absolute -bottom-1 left-0 h-px bg-warm-500 transition-all duration-300 group-hover:w-full ${
                    isActive(link.path) ? 'w-full' : 'w-0'
                  }`}
                />
              </Link>
            ))}
          </div>

          {/* Reservation Button */}
          <div className="hidden lg:flex items-center">
            <Link
              href="/room"
              className="text-sm font-semibold text-white tracking-widest uppercase border border-white/60 hover:border-warm-500 hover:text-warm-400 px-6 py-2.5 rounded-full transition-all duration-300"
            >
              Reservation
            </Link>
          </div>

          {/* Mobile toggle */}
          <button
            onClick={() => setIsOpen(!isOpen)}
            className="lg:hidden text-white p-2"
            aria-label="Toggle menu"
          >
            {isOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      <div
        className={`lg:hidden transition-all duration-500 overflow-hidden bg-primary-950 ${
          isOpen ? 'max-h-screen' : 'max-h-0'
        }`}
      >
        <div className="px-6 py-6 space-y-4 border-t border-primary-800">
          {navLinks.map((link) => (
            <Link
              key={link.path}
              href={link.path}
              className={`block py-2 text-base font-medium transition-colors ${
                isActive(link.path)
                  ? 'text-warm-400'
                  : 'text-white/70 hover:text-white'
              }`}
            >
              {link.name}
            </Link>
          ))}
          <div className="pt-4 border-t border-primary-800">
            <Link
              href="/room"
              className="block text-center text-sm font-semibold text-white tracking-widest uppercase border border-white/60 px-6 py-3 rounded-full"
            >
              Reservation
            </Link>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
