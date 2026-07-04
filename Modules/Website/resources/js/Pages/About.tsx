import { Heart, Shield, Users, Star, MapPin, Award, ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';

const About = () => {
  const values = [
    {
      icon: Heart,
      title: 'Passion for Service',
      description: 'We treat every guest with warmth and dedication, ensuring memorable experiences.',
    },
    {
      icon: Shield,
      title: 'Integrity & Trust',
      description: 'We uphold the highest standards of honesty and transparency in all our dealings.',
    },
    {
      icon: Users,
      title: 'Community Focus',
      description: 'We celebrate our Nigerian heritage while welcoming guests from around the world.',
    },
    {
      icon: Award,
      title: 'Excellence',
      description: 'We continuously strive to exceed expectations in everything we do.',
    },
  ];

  const milestones = [
    { year: 'Established', title: 'A Vision Born', description: 'The Bridge Hotel was founded with a mission to bring world-class hospitality to Ibadan.' },
    { year: '50 Rooms', title: 'Growing Capacity', description: 'Expanded to 50 rooms en-suite including 2 connecting rooms and 7 luxury suites.' },
    { year: '3 Restaurants', title: 'Culinary Excellence', description: 'Launched three distinct dining venues offering Chinese, Intercontinental, and Nigerian cuisine.' },
    { year: 'Today', title: 'Leading Hotel', description: 'Recognized as one of the premier luxury hotels in Oyo State, Nigeria.' },
  ];

  return (
    <div className="overflow-hidden">
      {/* Hero Section */}
      <section className="relative pt-32 pb-24 lg:pt-40 lg:pb-32">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Hotel exterior"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-primary-950/95 to-primary-950/70" />
        </div>
        <div className="relative z-10 container-custom">
          <div className="max-w-3xl">
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">About Us</span>
            <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mt-3 mb-6">
              Our Story of Excellence
            </h1>
            <p className="text-white/90 text-xl leading-relaxed">
              The Bridge Hotel is a symbol of luxury and hospitality in the heart of Ibadan.
              We are dedicated to providing an exceptional experience for every guest who walks through our doors.
            </p>
          </div>
        </div>
      </section>

      {/* Mission Section */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div className="relative order-2 lg:order-1">
              <img
                src="https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?auto=compress&cs=tinysrgb&w=800"
                alt="Our team"
                className="rounded-xl shadow-xl w-full"
              />
              <div className="absolute -bottom-8 -right-8 w-48 h-48 bg-warm-500/20 rounded-xl -z-10" />
            </div>
            <div className="order-1 lg:order-2">
              <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Who We Are</span>
              <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3 mb-6">
                Welcome to The Bridge Hotel
              </h2>
              <div className="space-y-4 text-primary-600 text-lg leading-relaxed">
                <p>
                  Situated in the prestigious Kolapo Ishola GRA, Akobo, Ibadan, The Bridge Hotel stands as a beacon of
                  sophisticated hospitality in Oyo State, Nigeria. Our hotel is designed to offer a seamless blend of
                  contemporary luxury and traditional Nigerian warmth.
                </p>
                <p>
                  With 50 beautifully appointed rooms and suites, we cater to both leisure and business travelers
                  seeking comfort, convenience, and world-class service. Our commitment to excellence has made us
                  a preferred destination for discerning guests.
                </p>
                <p>
                  Whether you're visiting for business, a special celebration, or a relaxing getaway, our dedicated
                  team is here to ensure your stay exceeds all expectations.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Values Section */}
      <section className="bg-primary-50 section-padding">
        <div className="container-custom">
          <div className="text-center mb-16">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Our Core Values</span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              What We Stand For
            </h2>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value, index) => (
              <div
                key={index}
                className="bg-white rounded-xl p-8 text-center shadow-sm hover:shadow-xl transition-all duration-500"
              >
                <div className="w-16 h-16 bg-warm-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                  <value.icon className="w-8 h-8 text-warm-600" />
                </div>
                <h3 className="font-serif text-xl font-semibold text-primary-900 mb-3">
                  {value.title}
                </h3>
                <p className="text-primary-600">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Our Journey */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="text-center mb-16">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Our Journey</span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              Milestones & Achievements
            </h2>
          </div>
          <div className="relative">
            {/* Timeline Line */}
            <div className="hidden lg:block absolute left-1/2 transform -translate-x-1/2 w-px h-full bg-warm-200" />

            <div className="space-y-12">
              {milestones.map((milestone, index) => (
                <div
                  key={index}
                  className={`flex flex-col lg:flex-row items-center gap-8 ${
                    index % 2 === 0 ? 'lg:flex-row' : 'lg:flex-row-reverse'
                  }`}
                >
                  <div className={`lg:w-1/2 ${index % 2 === 0 ? 'lg:text-right' : 'lg:text-left'}`}>
                    <div className={`bg-white rounded-xl p-8 shadow-lg ${index % 2 === 0 ? 'lg:ml-auto' : 'lg:mr-auto'} max-w-md`}>
                      <span className="text-warm-500 font-bold text-sm uppercase tracking-wider">{milestone.year}</span>
                      <h3 className="font-serif text-xl font-semibold text-primary-900 mt-2 mb-3">
                        {milestone.title}
                      </h3>
                      <p className="text-primary-600">{milestone.description}</p>
                    </div>
                  </div>
                  <div className="hidden lg:flex w-4 h-4 bg-warm-500 rounded-full items-center justify-center z-10">
                    <Star className="w-2 h-2 text-white fill-white" />
                  </div>
                  <div className="lg:w-1/2" />
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Location Highlight */}
      <section className="relative py-24">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/4602481/pexels-photo-4602481.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Ibadan city"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-primary-950/85" />
        </div>
        <div className="relative z-10 container-custom">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div>
              <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">Our Location</span>
              <h2 className="font-serif text-4xl md:text-5xl font-bold text-white mt-3 mb-6">
                In the Heart of Ibadan
              </h2>
              <p className="text-white/80 text-lg leading-relaxed mb-8">
                Located in the serene Kolapo Ishola GRA, Akobo, our hotel offers easy access to the city's
                major attractions, business districts, and cultural landmarks. Experience the perfect balance
                of seclusion and connectivity.
              </p>
              <div className="flex items-start space-x-3 text-white/90">
                <MapPin className="w-6 h-6 text-warm-500 flex-shrink-0" />
                <span className="text-lg">Kolapo Ishola GRA, Akobo, Ibadan, Oyo State, Nigeria</span>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <img
                src="https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Pool area"
                className="rounded-lg shadow-xl w-full h-48 object-cover"
              />
              <img
                src="https://images.pexels.com/photos-2869275/pexels-photo-2869275.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Restaurant"
                className="rounded-lg shadow-xl w-full h-48 object-cover mt-8"
              />
              <img
                src="https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Room"
                className="rounded-lg shadow-xl w-full h-48 object-cover"
              />
              <img
                src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Lobby"
                className="rounded-lg shadow-xl w-full h-48 object-cover mt-8"
              />
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="bg-white section-padding">
        <div className="container-custom text-center">
          <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mb-6">
            Experience Our Hospitality
          </h2>
          <p className="text-primary-600 text-lg max-w-2xl mx-auto mb-10">
            Ready to discover what makes The Bridge Hotel special? Book your stay or get in touch with us today.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              href="/rooms"
              className="btn-primary inline-flex items-center space-x-2 text-lg"
            >
              <span>Book Your Stay</span>
              <ArrowRight className="w-5 h-5" />
            </Link>
            <Link href="/contact" className="btn-secondary text-lg">
              Contact Us
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
};

export default About;
