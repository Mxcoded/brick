import { useEffect, useMemo, useState } from 'react';
import { Calendar, Users, Bed, Wifi, Coffee, Check, X, ChevronRight, Info } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '../types';

interface Room {
  id: number;
  name: string;
  price: number;
  discountPrice?: number;
  capacity: number;
  beds: string;
  size: string;
  features: string[];
  image: string;
  available: boolean;
}

interface RoomTypeAmenity {
  name: string;
}

interface RoomTypeResource {
  id: number;
  name: string;
  price: string | number;
  capacity: number;
  bed_type?: string | null;
  size?: string | null;
  image_url?: string | null;
  units_count?: number;
  amenities?: RoomTypeAmenity[];
}

interface PaginatedRoomTypes {
  data: RoomTypeResource[];
}

interface RoomsPageProps extends PageProps {
  roomTypes?: PaginatedRoomTypes;
}

const fallbackRoomImage = 'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&cs=tinysrgb&w=800';

const Rooms = () => {
  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState(1);
  const [rooms, setRooms] = useState(1);
  const [showResults, setShowResults] = useState(false);
  const [selectedRoom, setSelectedRoom] = useState<Room | null>(null);
  const [showBookingForm, setShowBookingForm] = useState(false);
  const { roomTypes } = usePage().props as unknown as RoomsPageProps;

  useEffect(() => {
    console.log('Room Types:', roomTypes);
  }, [roomTypes]);

  const calculateNights = () => {
    if (!checkIn || !checkOut) return 0;
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
  };

  const availableRooms: Room[] = useMemo(() => {
    return (roomTypes?.data ?? []).map((roomType) => ({
      id: roomType.id,
      name: roomType.name,
      price: Number(roomType.price) || 0,
      capacity: roomType.capacity,
      beds: roomType.bed_type || 'Bed details available on request',
      size: roomType.size || 'Size available on request',
      features: roomType.amenities?.map((amenity) => amenity.name).filter(Boolean) ?? [],
      image: roomType.image_url || fallbackRoomImage,
      available: (roomType.units_count ?? 0) > 0,
    }));
  }, [roomTypes]);

  const handleCheckAvailability = (e: React.FormEvent) => {
    e.preventDefault();
    if (checkIn && checkOut && calculateNights() > 0) {
      setShowResults(true);
    }
  };

  const handleBookRoom = (room: Room) => {
    setSelectedRoom(room);
    setShowBookingForm(true);
  };

  const today = new Date().toISOString().split('T')[0];

  return (
    <div className="overflow-hidden">
      {/* Hero Section */}
      <section className="relative pt-32 pb-48 lg:pt-40 lg:pb-56">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Luxury room"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-primary-950/95 to-primary-950/70" />
        </div>
        <div className="relative z-10 container-custom">
          <div className="max-w-3xl">
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">Accommodations</span>
            <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mt-3 mb-6">
              Rooms & Suites
            </h1>
            <p className="text-white/90 text-xl leading-relaxed">
              Choose from our selection of 50 beautifully appointed rooms and suites,
              each designed to provide the perfect blend of comfort and luxury.
            </p>
          </div>
        </div>
      </section>

      {/* Booking Form */}
      <section className="relative z-20 -mt-32 mb-16">
        <div className="container-custom">
          <div className="bg-white rounded-xl shadow-2xl p-8">
            <h3 className="font-serif text-xl font-semibold text-primary-900 mb-6">Check Availability</h3>
            <form onSubmit={handleCheckAvailability} className="grid md:grid-cols-5 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium text-primary-700">Check In</label>
                <div className="relative">
                  <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-primary-400" />
                  <input
                    type="date"
                    value={checkIn}
                    onChange={(e) => setCheckIn(e.target.value)}
                    min={today}
                    className="w-full pl-10 pr-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all"
                    required
                  />
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium text-primary-700">Check Out</label>
                <div className="relative">
                  <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-primary-400" />
                  <input
                    type="date"
                    value={checkOut}
                    onChange={(e) => setCheckOut(e.target.value)}
                    min={checkIn || today}
                    className="w-full pl-10 pr-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all"
                    required
                  />
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium text-primary-700">Guests</label>
                <div className="relative">
                  <Users className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-primary-400" />
                  <select
                    value={guests}
                    onChange={(e) => setGuests(Number(e.target.value))}
                    className="w-full pl-10 pr-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all appearance-none"
                  >
                    {[1, 2, 3, 4, 5, 6].map((num) => (
                      <option key={num} value={num}>
                        {num} {num === 1 ? 'Guest' : 'Guests'}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium text-primary-700">Rooms</label>
                <div className="relative">
                  <Bed className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-primary-400" />
                  <select
                    value={rooms}
                    onChange={(e) => setRooms(Number(e.target.value))}
                    className="w-full pl-10 pr-4 py-3 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none transition-all appearance-none"
                  >
                    {[1, 2, 3, 4].map((num) => (
                      <option key={num} value={num}>
                        {num} {num === 1 ? 'Room' : 'Rooms'}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              <div className="flex items-end">
                <button type="submit" className="w-full btn-primary">
                  Check Availability
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      {/* Room Listings */}
      <section className="bg-primary-50 section-padding pt-0">
        <div className="container-custom">
          {/* Terms Notice */}
          <div className="bg-warm-500/10 border border-warm-500/20 rounded-lg p-4 mb-8 flex items-start space-x-3">
            <Info className="w-5 h-5 text-warm-600 flex-shrink-0 mt-0.5" />
            <div className="text-primary-700 text-sm">
              <p className="font-medium mb-1">Booking Terms:</p>
              <p>Check In: 2:00 PM | Check Out: 12:00 Noon. All rates include breakfast and free WiFi. Late check-out before 6:00 PM attracts 50% surcharge, full payment after 6:00 PM.</p>
            </div>
          </div>

          {!showResults && (
            <div className="text-center py-16">
              <div className="w-20 h-20 bg-primary-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <Calendar className="w-10 h-10 text-primary-500" />
              </div>
              <h3 className="font-serif text-2xl font-semibold text-primary-900 mb-3">
                Select Your Dates
              </h3>
              <p className="text-primary-600 max-w-md mx-auto">
                Enter your check-in and check-out dates above to view available rooms and prices.
              </p>
            </div>
          )}

          {availableRooms.length === 0 ? (
            <div className="text-center py-16">
              <div className="w-20 h-20 bg-primary-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <Bed className="w-10 h-10 text-primary-500" />
              </div>
              <h3 className="font-serif text-2xl font-semibold text-primary-900 mb-3">
                No Rooms Found
              </h3>
              <p className="text-primary-600 max-w-md mx-auto">
                No room types were returned for this page. Check that active room types exist for the selected property.
              </p>
            </div>
          ) : (
            <div>
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                  <h3 className="font-serif text-2xl font-semibold text-primary-900">Available Rooms</h3>
                  <p className="text-primary-600">
                    {calculateNights()} night{calculateNights() > 1 ? 's' : ''} · {guests} guest{guests > 1 ? 's' : ''} · {rooms} room{rooms > 1 ? 's' : ''}
                  </p>
                </div>
                <div className="flex items-center space-x-4 text-sm">
                  <div className="flex items-center space-x-1">
                    <div className="w-3 h-3 bg-green-500 rounded-full" />
                    <span className="text-primary-700">Available</span>
                  </div>
                  <div className="flex items-center space-x-1">
                    <div className="w-3 h-3 bg-red-500 rounded-full" />
                    <span className="text-primary-700">Unavailable</span>
                  </div>
                </div>
              </div>

              <div className="space-y-6">
                {availableRooms.map((room) => (
                  <div
                    key={room.id}
                    className={`bg-white rounded-xl shadow-sm overflow-hidden ${
                      room.available ? '' : 'opacity-60'
                    }`}
                  >
                    <div className="grid md:grid-cols-3 gap-6">
                      <div className="relative aspect-[4/3] md:aspect-auto">
                        <img
                            src={`${room.image}`} 
                          alt={room.name}
                          className="w-full h-full object-cover"
                        />
                        <div className="absolute top-4 left-4">
                          <span
                            className={`px-3 py-1 rounded-full text-sm font-medium ${
                              room.available
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                            }`}
                          >
                            {room.available ? 'Available' : 'Unavailable'}
                          </span>
                        </div>
                      </div>
                      <div className="md:col-span-2 p-6">
                        <div className="flex flex-col lg:flex-row justify-between gap-4">
                          <div>
                            <h4 className="font-serif text-2xl font-semibold text-primary-900 mb-2">
                              {room.name}
                            </h4>
                            <div className="flex flex-wrap items-center gap-4 text-primary-600 text-sm mb-4">
                              <span>{room.beds}</span>
                              <span className="w-1 h-1 bg-primary-300 rounded-full" />
                              <span>{room.size}</span>
                              <span className="w-1 h-1 bg-primary-300 rounded-full" />
                              <span>Max {room.capacity} guests</span>
                            </div>
                            <div className="flex flex-wrap gap-2">
                              {room.features.map((feature, idx) => (
                                <span
                                  key={idx}
                                  className="inline-flex items-center space-x-1 text-xs bg-primary-50 text-primary-700 px-2 py-1 rounded"
                                >
                                  {feature === 'Free WiFi' && <Wifi className="w-3 h-3" />}
                                  {feature === 'Breakfast Included' && <Coffee className="w-3 h-3" />}
                                  <Check className="w-3 h-3" />
                                  <span>{feature}</span>
                                </span>
                              ))}
                            </div>
                          </div>
                          <div className="text-right">
                            <div className="mb-2">
                              {room.discountPrice && (
                                <span className="text-primary-400 line-through text-sm mr-2">
                                  ₦{room.price.toLocaleString()}
                                </span>
                              )}
                              <span className="font-bold text-2xl text-primary-900">
                                ₦{(room.discountPrice || room.price).toLocaleString()}
                              </span>
                              <span className="text-primary-600 text-sm">/night</span>
                            </div>
                            <div className="text-warm-600 font-medium mb-4">
                              Total: ₦{((room.discountPrice || room.price) * calculateNights()).toLocaleString()}
                            </div>
                            <button
                              onClick={() => handleBookRoom(room)}
                              disabled={!room.available}
                              className={`btn-primary inline-flex items-center space-x-2 ${
                                !room.available ? 'opacity-50 cursor-not-allowed' : ''
                              }`}
                            >
                              <span>Book Now</span>
                              <ChevronRight className="w-4 h-4" />
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </section>

      {/* Booking Modal */}
      {showBookingForm && selectedRoom && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary-950/80">
          <div className="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-primary-100">
              <h3 className="font-serif text-2xl font-semibold text-primary-900">Complete Your Booking</h3>
            </div>
            <div className="p-6 space-y-6">
              {/* Room Summary */}
              <div className="flex gap-4 p-4 bg-primary-50 rounded-lg">
                <img
                  src={selectedRoom.image}
                  alt={selectedRoom.name}
                  className="w-24 h-24 object-cover rounded-lg"
                />
                <div>
                  <h4 className="font-semibold text-primary-900">{selectedRoom.name}</h4>
                  <p className="text-sm text-primary-600">{calculateNights()} night{calculateNights() > 1 ? 's' : ''}</p>
                  <p className="text-warm-600 font-bold mt-1">
                    ₦{((selectedRoom.discountPrice || selectedRoom.price) * calculateNights()).toLocaleString()}
                  </p>
                </div>
              </div>

              {/* Guest Details Form */}
              <form className="space-y-4">
                <h4 className="font-medium text-primary-900">Guest Information</h4>
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">First Name</label>
                    <input
                      type="text"
                      className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                      placeholder="John"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">Last Name</label>
                    <input
                      type="text"
                      className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                      placeholder="Doe"
                    />
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Email</label>
                  <input
                    type="email"
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    placeholder="john@example.com"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Phone Number</label>
                  <input
                    type="tel"
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    placeholder="+234 800 000 0000"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Special Requests (Optional)</label>
                  <textarea
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none resize-none"
                    rows={3}
                    placeholder="Any special requests or requirements..."
                  />
                </div>
              </form>
            </div>
            <div className="p-6 border-t border-primary-100 flex gap-4">
              <button
                onClick={() => setShowBookingForm(false)}
                className="flex-1 btn-secondary"
              >
                Cancel
              </button>
              <button
                onClick={() => {
                  alert('Booking submitted successfully! (This is a demo - no actual booking is made)');
                  setShowBookingForm(false);
                }}
                className="flex-1 btn-primary"
              >
                Confirm Booking
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Rooms;
