import { useState, useEffect } from 'react';
import { Clock, Users, Star, X, Check, Sparkles, Leaf, Waves, Wind, AlertCircle } from 'lucide-react';

interface SpaService {
  id: number;
  name: string;
  category: string;
  description: string | null;
  duration_minutes: number;
  price: number;
  is_available: boolean;
  display_order: number;
}

interface BookingForm {
  service_id: string;
  appointment_date: string;
  appointment_time: string;
  guest_name: string;
  guest_email: string;
  guest_phone: string;
  number_of_guests: number;
  special_requests: string;
}

const categoryConfig: Record<string, { icon: typeof Sparkles; color: string; bg: string }> = {
  Massage: { icon: Waves, color: 'text-warm-600', bg: 'bg-warm-50' },
  Facial: { icon: Sparkles, color: 'text-rose-600', bg: 'bg-rose-50' },
  'Body Treatment': { icon: Leaf, color: 'text-green-700', bg: 'bg-green-50' },
  Wellness: { icon: Wind, color: 'text-sky-700', bg: 'bg-sky-50' },
};

const formatPrice = (price: number) =>
  new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', minimumFractionDigits: 0 }).format(price);

const timeSlots = [
  '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
  '11:00', '11:30', '12:00', '13:00', '13:30', '14:00',
  '14:30', '15:00', '15:30', '16:00', '16:30', '17:00',
  '17:30', '18:00', '18:30', '19:00',
];

const Spa = () => {
  const [services, setServices] = useState<SpaService[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeCategory, setActiveCategory] = useState('All');
  const [selectedService, setSelectedService] = useState<SpaService | null>(null);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [form, setForm] = useState<BookingForm>({
    service_id: '',
    appointment_date: '',
    appointment_time: '',
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    number_of_guests: 1,
    special_requests: '',
  });

  useEffect(() => {
    const fetchServices = async () => {
      // const { data } = await supabase
      //   .from('spa_services')
      //   .select('*')
      //   .eq('is_available', true)
      //   .order('display_order', { ascending: true });
      // if (data) setServices(data);
      setLoading(false);
    };
    fetchServices();
  }, []);

  const categories = ['All', ...Array.from(new Set(services.map((s) => s.category)))];

  const filtered =
    activeCategory === 'All' ? services : services.filter((s) => s.category === activeCategory);

  const openBooking = (service: SpaService) => {
    setSelectedService(service);
    setForm((prev) => ({ ...prev, service_id: String(service.id) }));
    setBookingSuccess(false);
    setError(null);
  };

  const closeModal = () => {
    setSelectedService(null);
    setBookingSuccess(false);
    setError(null);
    setForm({
      service_id: '',
      appointment_date: '',
      appointment_time: '',
      guest_name: '',
      guest_email: '',
      guest_phone: '',
      number_of_guests: 1,
      special_requests: '',
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);

    // const { error: insertError } = await supabase.from('spa_appointments').insert([
    //   {
    //     service_id: parseInt(form.service_id),
    //     appointment_date: form.appointment_date,
    //     appointment_time: form.appointment_time,
    //     guest_name: form.guest_name,
    //     guest_email: form.guest_email,
    //     guest_phone: form.guest_phone || null,
    //     number_of_guests: form.number_of_guests,
    //     special_requests: form.special_requests || null,
    //     status: 'pending',
    //  },
   // ]);

    //if (insertError) {
      //setError('Failed to submit appointment. Please try again.');
    //} else {
      //setBookingSuccess(true);
    //}
    //setSubmitting(false);
  };

  const today = new Date().toISOString().split('T')[0];

  return (
    <div className="overflow-hidden">
      {/* ── Hero ── */}
      <section className="relative pt-32 pb-24 lg:pt-40 lg:pb-32">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Spa & Wellness"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-primary-950/95 to-primary-950/65" />
        </div>
        <div className="relative z-10 container-custom">
          <div className="max-w-3xl">
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">
              Spa & Wellness
            </span>
            <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mt-3 mb-6">
              Rejuvenate Body & Soul
            </h1>
            <p className="text-white/85 text-xl leading-relaxed max-w-2xl">
              Escape the everyday at The Bridge Hotel Spa. Our expert therapists blend ancient
              healing traditions with modern techniques to restore harmony and vitality.
            </p>
            <div className="flex flex-wrap gap-8 mt-10">
              {[
                { label: 'Expert Therapists', value: '10+' },
                { label: 'Treatments', value: '13' },
                { label: 'Private Suites', value: '5' },
              ].map((s) => (
                <div key={s.label}>
                  <div className="font-serif text-4xl font-bold text-warm-400">{s.value}</div>
                  <div className="text-white/60 text-sm mt-1 uppercase tracking-wider">{s.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ── Experience Strip ── */}
      <section className="bg-primary-950 py-10">
        <div className="container-custom">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {[
              { icon: Clock, title: 'Daily 9am – 9pm', sub: 'Open every day' },
              { icon: Users, title: 'Private Suites', sub: 'Couples & solo' },
              { icon: Leaf, title: 'Natural Products', sub: 'Premium organic' },
              { icon: Star, title: 'Expert Therapists', sub: 'Certified & trained' },
            ].map((item) => (
              <div key={item.title} className="flex items-center space-x-4">
                <div className="w-10 h-10 bg-warm-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                  <item.icon className="w-5 h-5 text-warm-400" />
                </div>
                <div>
                  <div className="text-white text-sm font-semibold">{item.title}</div>
                  <div className="text-primary-400 text-xs">{item.sub}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Services ── */}
      <section className="bg-primary-50 section-padding">
        <div className="container-custom">
          <div className="text-center mb-12">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">
              Our Treatments
            </span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              Spa Menu
            </h2>
            <p className="text-primary-600 text-lg mt-4 max-w-2xl mx-auto">
              Each treatment is thoughtfully crafted to deliver profound relaxation and lasting results
            </p>
          </div>

          {/* Category Filter */}
          <div className="flex flex-wrap justify-center gap-3 mb-12">
            {loading
              ? null
              : categories.map((cat) => (
                  <button
                    key={cat}
                    onClick={() => setActiveCategory(cat)}
                    className={`px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300 ${
                      activeCategory === cat
                        ? 'bg-primary-900 text-white shadow-md'
                        : 'bg-white text-primary-700 border border-primary-200 hover:border-primary-400'
                    }`}
                  >
                    {cat}
                  </button>
                ))}
          </div>

          {loading ? (
            <div className="flex justify-center py-16">
              <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-warm-600" />
            </div>
          ) : (
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filtered.map((service) => {
                const config = categoryConfig[service.category] || categoryConfig['Wellness'];
                const Icon = config.icon;
                return (
                  <div
                    key={service.id}
                    className="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden group"
                  >
                    <div className={`${config.bg} px-6 pt-6 pb-4`}>
                      <div className="flex items-center justify-between mb-3">
                        <span
                          className={`inline-flex items-center space-x-1.5 text-xs font-semibold uppercase tracking-wider ${config.color}`}
                        >
                          <Icon className="w-3.5 h-3.5" />
                          <span>{service.category}</span>
                        </span>
                        <span className="flex items-center text-primary-500 text-xs">
                          <Clock className="w-3 h-3 mr-1" />
                          {service.duration_minutes} min
                        </span>
                      </div>
                      <h3 className="font-serif text-xl font-semibold text-primary-900 group-hover:text-primary-700 transition-colors">
                        {service.name}
                      </h3>
                    </div>
                    <div className="px-6 py-5 flex flex-col gap-4">
                      <p className="text-primary-600 text-sm leading-relaxed">
                        {service.description}
                      </p>
                      <div className="flex items-center justify-between pt-2 border-t border-primary-100">
                        <div>
                          <span className="font-serif text-2xl font-bold text-primary-900">
                            {formatPrice(service.price)}
                          </span>
                          <span className="text-primary-400 text-xs ml-1">/ session</span>
                        </div>
                        <button
                          onClick={() => openBooking(service)}
                          className="btn-accent text-sm py-2 px-4"
                        >
                          Book Now
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </section>

      {/* ── Why Choose Us ── */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            <div>
              <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">
                The Experience
              </span>
              <h2 className="font-serif text-4xl font-bold text-primary-900 mt-3 mb-6">
                A Sanctuary of Serenity
              </h2>
              <div className="space-y-5 text-primary-600 leading-relaxed">
                <p>
                  Our spa offers a tranquil escape from the demands of everyday life. From the moment
                  you arrive, you are immersed in an atmosphere of calm — warm lighting, soothing
                  scents, and the gentle sound of water.
                </p>
                <p>
                  Every treatment is delivered by our team of certified therapists who tailor each
                  session to your individual needs, ensuring a deeply personal and restorative experience.
                </p>
              </div>
              <div className="mt-8 grid grid-cols-2 gap-4">
                {[
                  'Certified therapists',
                  'Premium organic products',
                  'Private treatment suites',
                  'Couples packages',
                  'Steam & sauna access',
                  'Pre-natal massage',
                ].map((item) => (
                  <div key={item} className="flex items-center space-x-2">
                    <Check className="w-5 h-5 text-warm-600 flex-shrink-0" />
                    <span className="text-primary-700 text-sm">{item}</span>
                  </div>
                ))}
              </div>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <img
                src="https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Spa pool"
                className="rounded-xl shadow-lg w-full h-56 object-cover"
              />
              <img
                src="https://images.pexels.com/photos/3429265/pexels-photo-3429265.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Massage room"
                className="rounded-xl shadow-lg w-full h-56 object-cover mt-8"
              />
              <img
                src="https://images.pexels.com/photos/6560297/pexels-photo-6560297.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Spa treatment"
                className="rounded-xl shadow-lg w-full h-56 object-cover"
              />
              <img
                src="https://images.pexels.com/photos/3985329/pexels-photo-3985329.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt="Relaxation"
                className="rounded-xl shadow-lg w-full h-56 object-cover -mt-8"
              />
            </div>
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section className="relative py-24 overflow-hidden">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/1851164/pexels-photo-1851164.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Spa ambience"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-primary-950/80" />
        </div>
        <div className="relative z-10 container-custom text-center">
          <h2 className="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
            Ready to Unwind?
          </h2>
          <p className="text-white/75 text-lg max-w-xl mx-auto mb-8">
            Book your spa treatment today and let our expert therapists guide you to total relaxation.
          </p>
          <button
            onClick={() => services.length && openBooking(services[0])}
            className="btn-accent inline-flex items-center space-x-2 text-base px-8 py-4"
          >
            <Sparkles className="w-5 h-5" />
            <span>Book a Treatment</span>
          </button>
        </div>
      </section>

      {/* ── Booking Modal ── */}
      {selectedService && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary-950/80 backdrop-blur-sm">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-y-auto">
            {/* Modal Header */}
            <div className="relative h-36 overflow-hidden rounded-t-2xl flex-shrink-0">
              <img
                src="https://images.pexels.com/photos/3429265/pexels-photo-3429265.jpeg?auto=compress&cs=tinysrgb&w=600"
                alt={selectedService.name}
                className="w-full h-full object-cover"
              />
              <div className="absolute inset-0 bg-primary-950/60" />
              <button
                onClick={closeModal}
                className="absolute top-3 right-3 w-8 h-8 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center transition-colors"
              >
                <X className="w-4 h-4 text-white" />
              </button>
              <div className="absolute bottom-4 left-5">
                <p className="text-warm-400 text-xs uppercase tracking-wider font-semibold">
                  {selectedService.category}
                </p>
                <h3 className="font-serif text-xl font-bold text-white">{selectedService.name}</h3>
                <div className="flex items-center space-x-3 text-white/70 text-xs mt-1">
                  <span className="flex items-center">
                    <Clock className="w-3 h-3 mr-1" />
                    {selectedService.duration_minutes} min
                  </span>
                  <span>{formatPrice(selectedService.price)}</span>
                </div>
              </div>
            </div>

            {bookingSuccess ? (
              <div className="p-8 text-center">
                <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <Check className="w-8 h-8 text-green-600" />
                </div>
                <h3 className="font-serif text-xl font-semibold text-primary-900 mb-2">
                  Appointment Booked!
                </h3>
                <p className="text-primary-600 mb-2">
                  Your {selectedService.name} appointment has been submitted.
                </p>
                <p className="text-primary-500 text-sm mb-6">
                  A confirmation will be sent to <strong>{form.guest_email}</strong>. Our team will
                  contact you to confirm the booking.
                </p>
                <button onClick={closeModal} className="btn-primary w-full">
                  Done
                </button>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="p-6 space-y-5">
                <h4 className="font-semibold text-primary-900 text-sm uppercase tracking-wider">
                  Appointment Details
                </h4>

                {/* Service selector if user opens modal without one */}
                {!form.service_id && (
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">
                      Treatment <span className="text-red-500">*</span>
                    </label>
                    <select
                      value={form.service_id}
                      onChange={(e) => setForm((p) => ({ ...p, service_id: e.target.value }))}
                      required
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    >
                      <option value="">Select a treatment...</option>
                      {services.map((s) => (
                        <option key={s.id} value={s.id}>
                          {s.name} — {formatPrice(s.price)}
                        </option>
                      ))}
                    </select>
                  </div>
                )}

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">
                      Date <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="date"
                      value={form.appointment_date}
                      onChange={(e) => setForm((p) => ({ ...p, appointment_date: e.target.value }))}
                      min={today}
                      required
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">
                      Time <span className="text-red-500">*</span>
                    </label>
                    <select
                      value={form.appointment_time}
                      onChange={(e) => setForm((p) => ({ ...p, appointment_time: e.target.value }))}
                      required
                      className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    >
                      <option value="">Select time</option>
                      {timeSlots.map((t) => (
                        <option key={t} value={t}>
                          {t}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">
                    Number of Guests
                  </label>
                  <select
                    value={form.number_of_guests}
                    onChange={(e) => setForm((p) => ({ ...p, number_of_guests: parseInt(e.target.value) }))}
                    className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                  >
                    {[1, 2, 3, 4].map((n) => (
                      <option key={n} value={n}>
                        {n} {n === 1 ? 'Guest' : 'Guests'}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="border-t border-primary-100 pt-4">
                  <h4 className="font-semibold text-primary-900 text-sm uppercase tracking-wider mb-4">
                    Your Details
                  </h4>
                  <div className="space-y-4">
                    <div>
                      <label className="text-sm font-medium text-primary-700 block mb-1">
                        Full Name <span className="text-red-500">*</span>
                      </label>
                      <input
                        type="text"
                        value={form.guest_name}
                        onChange={(e) => setForm((p) => ({ ...p, guest_name: e.target.value }))}
                        required
                        placeholder="Your full name"
                        className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="text-sm font-medium text-primary-700 block mb-1">
                          Email <span className="text-red-500">*</span>
                        </label>
                        <input
                          type="email"
                          value={form.guest_email}
                          onChange={(e) => setForm((p) => ({ ...p, guest_email: e.target.value }))}
                          required
                          placeholder="you@email.com"
                          className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                        />
                      </div>
                      <div>
                        <label className="text-sm font-medium text-primary-700 block mb-1">
                          Phone
                        </label>
                        <input
                          type="tel"
                          value={form.guest_phone}
                          onChange={(e) => setForm((p) => ({ ...p, guest_phone: e.target.value }))}
                          placeholder="+234 800 0000"
                          className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                        />
                      </div>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-primary-700 block mb-1">
                        Special Requests / Health Notes
                      </label>
                      <textarea
                        value={form.special_requests}
                        onChange={(e) => setForm((p) => ({ ...p, special_requests: e.target.value }))}
                        rows={3}
                        placeholder="Allergies, injuries, pressure preferences..."
                        className="w-full px-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none resize-none"
                      />
                    </div>
                  </div>
                </div>

                {error && (
                  <div className="flex items-center space-x-2 bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
                    <AlertCircle className="w-4 h-4 flex-shrink-0" />
                    <span>{error}</span>
                  </div>
                )}

                <div className="flex gap-3 pt-2">
                  <button
                    type="button"
                    onClick={closeModal}
                    className="flex-1 btn-secondary"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={submitting}
                    className="flex-1 btn-accent disabled:opacity-60 disabled:cursor-not-allowed"
                  >
                    {submitting ? 'Booking...' : 'Confirm Booking'}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default Spa;
