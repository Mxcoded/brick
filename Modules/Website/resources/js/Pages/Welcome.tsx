import { useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight, Star, UtensilsCrossed, Waves, Sparkles, Users, Shirt, Check, ArrowRight } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '../types';

const heroSlides = [
  {
    image: 'https://images.pexels.com/photos/2869275/pexels-photo-2869275.jpeg?auto=compress&cs=tinysrgb&w=1920',
  },
  {
    image: 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920',
  },
  {
    image: 'https://images.pexels.com/photos/1268855/pexels-photo-1268855.jpeg?auto=compress&cs=tinysrgb&w=1920',
  },
];

const amenities = [
  { icon: UtensilsCrossed, title: 'Restaurant', description: 'Enjoy fine dining with a selection of local and international dishes' },
  { icon: Waves, title: 'Swimming Pool', description: 'Relax and unwind at our serene and spacious swimming pool' },
  { icon: Sparkles, title: 'Spa & Massage', description: 'Rejuvenate with therapeutic treatments in our luxurious spa' },
  { icon: Users, title: 'Meeting Room', description: 'Host productive meetings in our fully equipped conference rooms' },
  { icon: Star, title: 'Quality Facilities', description: 'Experience top-notch amenities designed for your comfort' },
  { icon: Shirt, title: 'Laundry Service', description: 'Effortless cleaning services for all your clothing needs' },
];

const roomTypes = [
  {
    name: 'Bridge Classic',
    price: '90,100',
    originalPrice: '106,000',
    image: 'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&cs=tinysrgb&w=600',
    features: ['King Bed', 'Free WiFi', 'Breakfast Included'],
  },
  {
    name: 'Bridge Executive Suite',
    price: '125,375',
    originalPrice: '147,500',
    image: 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=600',
    features: ['King Bed', 'Living Area', 'Premium Amenities'],
  },
  {
    name: 'Presidential Suite',
    price: '350,000',
    originalPrice: '',
    image: 'https://images.pexels.com/photos/1579253/pexels-photo-1579253.jpeg?auto=compress&cs=tinysrgb&w=600',
    features: ['Multiple Rooms', 'Butler Service', 'Exclusive Access'],
  },
];

const restaurants = [
  {
    name: 'Dragon Palace',
    type: 'Chinese Restaurant',
    desc: 'Authentic flavors from the Far East — dim sum, Peking duck, and classic stir-fry dishes.',
    image: 'https://images.pexels.com/photos/1414234/pexels-photo-1414234.jpeg?auto=compress&cs=tinysrgb&w=600',
    path: '/restaurant',
  },
  {
    name: 'Bridge Executive Lounge',
    type: 'Private Restaurant',
    desc: 'Exclusive intercontinental dining with filet mignon, lobster thermidor, and fine wines.',
    image: 'https://images.pexels.com/photos/3184192/pexels-photo-3184192.jpeg?auto=compress&cs=tinysrgb&w=600',
    path: '/restaurant',
  },
  {
    name: 'Oduwa Kitchen',
    type: 'Local Restaurant',
    desc: 'Celebrating Nigerian heritage — jollof rice, egusi soup, suya, and local delicacies.',
    image: 'https://images.pexels.com/photos/2290071/pexels-photo-2290071.jpeg?auto=compress&cs=tinysrgb&w=600',
    path: '/restaurant',
  },
];

const Welcome = () => {
  const [currentSlide, setCurrentSlide] = useState(0);
      const { auth, settings, featuredRooms, testimonials, dining, og_title, meta_description } = usePage().props as unknown as PageProps;
    const user = auth.user;


  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
      console.log("featuredRooms:", settings.property_name);
    }, 5500);
    return () => clearInterval(interval);
  }, []);

  const prevSlide = () => {
    setCurrentSlide((prev) => (prev - 1 + heroSlides.length) % heroSlides.length);
  };

  const nextSlide = () => {
    setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
  };

  return (
    <div className="overflow-hidden">
      {/* ─── Hero Section ─── */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-black">
        {/* Slides */}
        {heroSlides.map((slide, idx) => (
          <div
            key={idx}
            className={`absolute inset-0 transition-opacity duration-1000 ${
              idx === currentSlide ? 'opacity-100' : 'opacity-0'
            }`}
          >
            <img
              src={slide.image}
              alt={`${settings.property_name} Hotel`}
              className="w-full h-full object-cover opacity-60"
            />
          </div>
        ))}

        {/* Dark vignette overlay */}
        <div className="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/60 z-10" />

        {/* Left Arrow */}
        <button
          onClick={prevSlide}
          aria-label="Previous slide"
          className="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-30 w-10 h-10 flex items-center justify-center text-white/70 hover:text-white transition-colors"
        >
          <ChevronLeft className="w-8 h-8" />
        </button>

        {/* Right Arrow */}
        <button
          onClick={nextSlide}
          aria-label="Next slide"
          className="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-30 w-10 h-10 flex items-center justify-center text-white/70 hover:text-white transition-colors"
        >
          <ChevronRight className="w-8 h-8" />
        </button>

        {/* Arch Overlay */}
        <div className="relative z-20 flex items-center justify-center w-full px-4">
          <div className="arch-container relative w-full max-w-[560px] mx-auto">
            {/* SVG arch border */}
            <svg
              viewBox="0 0 560 640"
              className="absolute inset-0 w-full h-full pointer-events-none"
              preserveAspectRatio="none"
            >
              <path
                d="M 10 640 L 10 280 Q 10 10 280 10 Q 550 10 550 280 L 550 640"
                fill="none"
                stroke="#c9a96e"
                strokeWidth="1.2"
                opacity="0.7"
              />
            </svg>

            {/* Frosted glass content panel */}
            <div
              className="arch-content relative mx-auto flex flex-col items-center justify-center text-center px-10 pt-16 pb-12"
              style={{
                background: 'rgba(18, 18, 40, 0.55)',
                backdropFilter: 'blur(10px)',
                WebkitBackdropFilter: 'blur(10px)',
                borderRadius: '50% 50% 0 0 / 20% 20% 0 0',
                minHeight: '600px',
              }}
            >
              {/* Stars */}
              <div className="flex items-center justify-center space-x-2 mb-6">
                {[1, 2, 3, 4, 5].map((s) => (
                  <Star
                    key={s}
                    className="w-5 h-5"
                    style={{ color: '#e5a263', fill: '#e5a263' }}
                  />
                ))}
              </div>

              {/* Title */}
              <h1 className="font-serif text-5xl md:text-6xl font-bold text-white leading-tight mb-6 tracking-tight">
                {settings.property_name} Hotel
              </h1>

              {/* Description */}
              <p className="text-white/80 text-base md:text-lg leading-relaxed max-w-sm mb-10">
                {meta_description}
              </p>

              {/* CTA Button */}
              <Link
                href="/frontend/room"
                className="inline-flex items-center justify-center px-8 py-3 border border-warm-500 text-white text-sm font-semibold uppercase tracking-[0.15em] transition-all duration-300 hover:bg-warm-600 hover:border-warm-600"
              >
                Discover Rooms
              </Link>
            </div>
          </div>
        </div>

        {/* Slide dots */}
        <div className="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex space-x-2">
          {heroSlides.map((_, idx) => (
            <button
              key={idx}
              onClick={() => setCurrentSlide(idx)}
              className={`h-1.5 rounded-full transition-all duration-300 ${
                idx === currentSlide
                  ? 'bg-warm-500 w-6'
                  : 'bg-white/40 w-2 hover:bg-white/70'
              }`}
            />
          ))}
        </div>
      </section>

      {/* ─── Welcome Section ─── */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div>
              <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Welcome</span>
              <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3 mb-6">
                Experience Hospitality Like Never Before
              </h2>
              <p className="text-primary-600 text-lg leading-relaxed mb-6">
                {meta_description}
              </p>
              <div className="grid grid-cols-2 gap-3 mb-8">
                {['Free WiFi', 'Swimming Pool', 'Spa Services', '24/7 Reception', 'Restaurant', 'Free Parking'].map((item) => (
                  <div key={item} className="flex items-center space-x-2">
                    <Check className="w-5 h-5 text-warm-600 flex-shrink-0" />
                    <span className="text-primary-700">{item}</span>
                  </div>
                ))}
              </div>
              <div className="flex flex-wrap gap-4">
                <Link href="/frontend/room" className="btn-primary inline-flex items-center space-x-2">
                  <span>Book Your Stay</span>
                  <ArrowRight className="w-4 h-4" />
                </Link>
                <Link href="/gallery" className="btn-secondary">
                  View Gallery
                </Link>
              </div>
            </div>
            <div className="relative">
              <div className="grid grid-cols-2 gap-4">
                <img
                  src="https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&cs=tinysrgb&w=600"
                  alt="Hotel interior"
                  className="rounded-lg shadow-xl w-full h-64 object-cover"
                />
                <img
                  src="https://images.pexels.com/photos/210605/pexels-photo-210605.jpeg?auto=compress&cs=tinysrgb&w=600"
                  alt="Hotel suite"
                  className="rounded-lg shadow-xl w-full h-64 object-cover mt-8"
                />
                <img
                  src="https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=600"
                  alt="Hotel pool"
                  className="rounded-lg shadow-xl w-full h-64 object-cover"
                />
                <img
                  src="https://images.pexels.com/photos/1579253/pexels-photo-1579253.jpeg?auto=compress&cs=tinysrgb&w=600"
                  alt="Hotel bedroom"
                  className="rounded-lg shadow-xl w-full h-64 object-cover -mt-8"
                />
              </div>
              {/* Stats badge */}
              <div className="absolute -bottom-6 left-1/2 -translate-x-1/2 bg-primary-900 text-white rounded-xl shadow-2xl px-8 py-4 flex items-center space-x-6">
                <div className="text-center">
                  <div className="font-serif text-3xl font-bold text-warm-400">50+</div>
                  <div className="text-xs text-primary-300 mt-1">Luxury Rooms</div>
                </div>
                <div className="w-px h-10 bg-primary-700" />
                <div className="text-center">
                  <div className="font-serif text-3xl font-bold text-warm-400">3</div>
                  <div className="text-xs text-primary-300 mt-1">Restaurants</div>
                </div>
                <div className="w-px h-10 bg-primary-700" />
                <div className="text-center">
                  <div className="font-serif text-3xl font-bold text-warm-400">5★</div>
                  <div className="text-xs text-primary-300 mt-1">Service</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── Amenities Section ─── */}
      <section className="bg-primary-950 section-padding">
        <div className="container-custom">
          <div className="text-center mb-14">
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">Our Facilities</span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-white mt-3">
              World-Class Amenities
            </h2>
            <p className="text-primary-300 text-lg mt-4 max-w-2xl mx-auto">
              Everything you need for a perfect stay, from relaxing spa treatments to productive meetings
            </p>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {amenities.map((amenity, idx) => (
              <div
                key={idx}
                className="group flex items-start space-x-5 p-6 rounded-xl border border-primary-800 hover:border-warm-600 transition-all duration-300 hover:bg-primary-900"
              >
                <div className="w-12 h-12 bg-warm-600/20 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-warm-600/30 transition-colors">
                  <amenity.icon className="w-6 h-6 text-warm-400" />
                </div>
                <div>
                  <h3 className="font-serif text-lg font-semibold text-white mb-2">{amenity.title}</h3>
                  <p className="text-primary-400 text-sm leading-relaxed">{amenity.description}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ─── Rooms Section ─── */}
      <section className="bg-primary-50 section-padding">
        <div className="container-custom">
          <div className="text-center mb-14">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Accommodation</span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              Our Rates
            </h2>
            <p className="text-primary-600 text-lg mt-4 max-w-2xl mx-auto">
              All rates include single breakfast and free WiFi
            </p>
          </div>

          <div className="grid md:grid-cols-3 gap-8 mb-10">
            {featuredRooms.map((room, idx) => (
              <div key={idx} className="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500">
                <div className="relative h-56 overflow-hidden">
                  <img
                    src={`${room.image_url}`} 
                    alt={room.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  {room.price && (
                    <div className="absolute top-4 right-4 bg-warm-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                      10% OFF
                    </div>
                  )}
                </div>
                <div className="p-6">
                  <h3 className="font-serif text-xl font-semibold text-primary-900 mb-3">{room.name}</h3>
                  <div className="flex flex-wrap gap-2 mb-4">
                    {room.amenities.map((f, fi) => (
                      <span key={fi} className="flex items-center space-x-1 text-xs text-primary-600 bg-primary-50 px-2 py-1 rounded">
                        <Check className="w-3 h-3 text-warm-500" />
                        <span>{f.name}</span>
                      </span>
                    ))}
                  </div>
                  <div className="flex items-baseline justify-between mt-4 pt-4 border-t border-primary-100">
                    <div>
                      <span className="font-serif text-2xl font-bold text-primary-900">
                        ₦{room.price}
                      </span>
                      {room.originalPrice && (
                        <span className="text-sm text-primary-400 line-through ml-2">₦{room.originalPrice}</span>
                      )}
                      <p className="text-xs text-primary-400 mt-1">per night</p>
                    </div>
                    <Link
                      href="/frontend/room"
                      className="btn-primary text-sm py-2 px-4"
                    >
                      Book
                    </Link>
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="text-center">
            <Link href="/frontend/room" className="btn-primary inline-flex items-center space-x-2">
              <span>View All Rooms</span>
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      </section>

      {/* ─── Rates Table ─── */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="text-center mb-12">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Pricing</span>
            <h2 className="font-serif text-4xl font-bold text-primary-900 mt-3">
              Our Rates with 10% Discount
            </h2>
          </div>
          <div className="max-w-3xl mx-auto overflow-hidden rounded-xl shadow-lg border border-primary-100">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-primary-900 text-white">
                  <th className="text-left px-6 py-4 font-serif font-semibold">Room Type</th>
                  <th className="text-right px-6 py-4 font-serif font-semibold">Rate (₦)</th>
                  <th className="text-right px-6 py-4 font-serif font-semibold">Discounted (₦)</th>
                </tr>
              </thead>
              <tbody>
                {featuredRooms.map((row, idx) => (
                  <tr
                    key={idx}
                    className={`border-b border-primary-100 ${idx % 2 === 0 ? 'bg-white' : 'bg-primary-50'}`}
                  >
                    <td className="px-6 py-4 font-medium text-primary-800">{row.name}</td>
                    <td className="px-6 py-4 text-right text-primary-600">{row.price}</td>
                    <td className="px-6 py-4 text-right font-semibold text-warm-700">{row.price}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <p className="text-center text-primary-500 text-sm mt-6">
            All rates include Single Breakfast & Free WiFi. Check-In: 2:00PM | Check-Out: 12:00PM
          </p>
        </div>
      </section>

      {/* ─── Restaurants Section ─── */}
      <section className="bg-primary-50 section-padding">
        <div className="container-custom">
          <div className="text-center mb-14">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Dining</span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              Three Distinct Dining Experiences
            </h2>
            <p className="text-primary-600 text-lg mt-4 max-w-2xl mx-auto">
              From Chinese cuisine to intercontinental dishes and authentic Nigerian flavors
            </p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            {restaurants.map((r, idx) => (
              <Link
                key={idx}
                href={r.path}
                className="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 block"
              >
                <div className="relative h-56 overflow-hidden">
                  <img
                    src={r.image}
                    alt={r.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-primary-950/70 to-transparent" />
                  <span className="absolute top-4 left-4 bg-warm-600 text-white text-xs font-medium px-3 py-1 rounded-full">
                    {r.type}
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="font-serif text-xl font-semibold text-primary-900 mb-2">{r.name}</h3>
                  <p className="text-primary-600 text-sm leading-relaxed mb-4">{r.desc}</p>
                  <span className="inline-flex items-center space-x-2 text-warm-700 font-medium text-sm group-hover:text-warm-600 transition-colors">
                    <span>View Menu</span>
                    <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                  </span>
                </div>
              </Link>
            ))}
          </div>
          <div className="text-center mt-12">
            <Link href="/restaurant" className="btn-primary inline-flex items-center space-x-2">
              <span>Explore All Restaurants</span>
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      </section>

      {/* ─── CTA Section ─── */}
      <section className="relative py-28 overflow-hidden">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/1134176/pexels-photo-1134176.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Hotel exterior"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-primary-950/75" />
        </div>
        <div className="relative z-10 container-custom text-center">
          <span className="text-warm-400 font-medium text-sm uppercase tracking-wider">Limited Offer</span>
          <h2 className="font-serif text-4xl md:text-5xl font-bold text-white mt-3 mb-6">
            Ready to Experience Luxury?
          </h2>
          <p className="text-white/80 text-lg max-w-2xl mx-auto mb-10">
            Book your stay at The Bridge Hotel and create unforgettable memories in the heart of Ibadan.
            Enjoy 10% off when you book directly.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link href="/frontend/room" className="btn-accent inline-flex items-center justify-center space-x-2">
              <span>Book Now</span>
              <ArrowRight className="w-4 h-4" />
            </Link>
            <Link
              href="/contact"
              className="btn-secondary border-white text-white hover:bg-white hover:text-primary-900 inline-flex items-center justify-center"
            >
              Contact Us
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Welcome;
