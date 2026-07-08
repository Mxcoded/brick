import { useState } from 'react';
import { Utensils, Clock, Users, Phone, ChevronRight, Star, Leaf, Flame } from 'lucide-react';

interface MenuItem {
  name: string;
  description: string;
  price: string;
  dietary?: string[];
}

interface MenuCategory {
  name: string;
  items: MenuItem[];
}

interface Restaurant {
  id: string;
  name: string;
  tagline: string;
  type: string;
  description: string;
  hours: string;
  image: string;
  menu: MenuCategory[];
}

const Restaurant = () => {
  const [selectedRestaurant, setSelectedRestaurant] = useState<Restaurant | null>(null);
  const [showBookingModal, setShowBookingModal] = useState(false);
  const [selectedTab, setSelectedTab] = useState(0);

  const restaurants: Restaurant[] = [
    {
      id: 'chinese',
      name: 'Dragon Palace',
      tagline: 'Authentic Chinese Cuisine',
      type: 'Chinese Restaurant',
      description: 'Experience the rich flavors of authentic Chinese cuisine at Dragon Palace. Our master chefs bring you traditional recipes from various regions of China, from savory dim sum to exquisite Peking duck.',
      hours: '11:00 AM - 11:00 PM',
      image: 'https://images.pexels.com/photos-2609227/pexels-photo-2609227.jpeg?auto=compress&cs=tinysrgb&w=800',
      menu: [
        {
          name: 'Appetizers',
          items: [
            { name: 'Spring Rolls', description: 'Crispy rolls filled with vegetables and glass noodles', price: '4,500', dietary: ['vegetarian'] },
            { name: 'Dim Sum Platter', description: 'Assorted steamed dumplings - shrimp, pork, and vegetable', price: '8,500' },
            { name: 'Hot & Sour Soup', description: 'Traditional soup with tofu, mushrooms, and bamboo shoots', price: '3,500' },
            { name: 'Crispy Prawns', description: 'Golden fried prawns with sweet chili sauce', price: '9,500' },
          ],
        },
        {
          name: 'Main Courses',
          items: [
            { name: 'Peking Duck', description: 'Roasted duck with crispy skin, served with pancakes and hoisin sauce', price: '25,000' },
            { name: 'Kung Pao Chicken', description: 'Spicy stir-fried chicken with peanuts and vegetables', price: '12,000' },
            { name: 'Szechuan Beef', description: 'Sliced beef in fiery Szechuan sauce with bell peppers', price: '14,500' },
            { name: 'Cantonese Lobster', description: 'Fresh lobster with ginger and scallion sauce', price: '35,000' },
            { name: 'Mapo Tofu', description: 'Silken tofu in spicy meat sauce', price: '8,500', dietary: ['spicy'] },
          ],
        },
        {
          name: 'Noodles & Rice',
          items: [
            { name: 'Yangzhou Fried Rice', description: 'Classic fried rice with shrimp, ham, and vegetables', price: '7,500' },
            { name: 'Beef Chow Mein', description: 'Stir-fried noodles with tender beef strips', price: '11,000' },
            { name: 'Seafood Fried Rice', description: 'Rice with prawns, squid, and fish', price: '13,500' },
            { name: 'Dan Dan Noodles', description: 'Spicy Szechuan noodles with minced pork', price: '9,500', dietary: ['spicy'] },
          ],
        },
        {
          name: 'Desserts',
          items: [
            { name: 'Mango Pudding', description: 'Creamy mango pudding topped with fresh fruit', price: '4,000' },
            { name: 'Red Bean Buns', description: 'Steamed buns filled with sweet red bean paste', price: '3,500' },
            { name: 'Sesame Balls', description: 'Fried glutinous rice balls with sesame coating', price: '3,000' },
          ],
        },
      ],
    },
    {
      id: 'intercontinental',
      name: 'Bridge Executive Lounge',
      tagline: 'Private International Dining',
      type: 'Private Restaurant',
      description: 'An exclusive fine dining experience featuring intercontinental cuisine. From Mediterranean favorites to American classics, enjoy world-class dishes in an elegant private setting perfect for special occasions.',
      hours: '6:00 PM - 10:00 PM',
      image: 'https://images.pexels.com/photos-2869275/pexels-photo-2869275.jpeg?auto=compress&cs=tinysrgb&w=800',
      menu: [
        {
          name: 'Starters',
          items: [
            { name: 'Truffle Bruschetta', description: 'Toasted ciabatta with truffle-infused tomatoes and basil', price: '6,500' },
            { name: 'Grilled Prawns Provencal', description: 'Jumbo prawns with garlic butter and herbs', price: '12,000' },
            { name: 'Caesar Salad', description: 'Romaine lettuce with parmesan, croutons, and caesar dressing', price: '5,500' },
            { name: 'Beef Carpaccio', description: 'Thinly sliced raw beef with arugula and capers', price: '9,500' },
          ],
        },
        {
          name: 'Main Courses',
          items: [
            { name: 'Grilled Atlantic Salmon', description: 'Pan-seared salmon with asparagus and lemon butter sauce', price: '18,500' },
            { name: 'Filet Mignon', description: 'Tender beef fillet with red wine reduction and roasted potatoes', price: '28,000' },
            { name: 'Lobster Thermidor', description: 'Baked lobster in rich cheese sauce', price: '45,000' },
            { name: 'Chicken Cordon Bleu', description: 'Stuffed chicken breast with ham and gruyere cheese', price: '15,500' },
            { name: 'Lamb Rack', description: 'Herb-crusted lamb with mint jus', price: '25,000' },
            { name: 'Mushroom Risotto', description: 'Creamy arborio rice with wild mushrooms and truffle oil', price: '11,000', dietary: ['vegetarian'] },
          ],
        },
        {
          name: 'Desserts',
          items: [
            { name: 'Creme Brulee', description: 'Vanilla custard with caramelized sugar crust', price: '5,500' },
            { name: 'Chocolate Fondant', description: 'Warm chocolate cake with molten center, served with vanilla ice cream', price: '7,000' },
            { name: 'Tiramisu', description: 'Italian coffee-flavored layered dessert', price: '6,000' },
            { name: 'Cheese Platter', description: 'Selection of aged cheeses with crackers and fruits', price: '9,500' },
          ],
        },
      ],
    },
    {
      id: 'nigerian',
      name: 'Oduwa Kitchen',
      tagline: 'Authentic Nigerian Flavors',
      type: 'Local Restaurant',
      description: 'Celebrate the vibrant tastes of Nigeria at Oduwa Kitchen. Our restaurant brings you traditional dishes from across the country, prepared with authentic recipes and the freshest local ingredients.',
      hours: '7:00 AM - 10:00 PM',
      image: 'https://images.pexels.com/photos/2290071/pexels-photo-2290071.jpeg?auto=compress&cs=tinysrgb&w=800',
      menu: [
        {
          name: 'Breakfast',
          items: [
            { name: 'Akara & Pap', description: 'Fried bean cakes served with fermented corn pudding', price: '2,500' },
            { name: 'Yam & Eggs', description: 'Fried or boiled yam with scrambled eggs', price: '3,000' },
            { name: 'Moi Moi', description: 'Steamed bean pudding with spices and fish', price: '2,500' },
            { name: 'Nigerian Pancakes', description: 'Sweet and spicy pancakes with honey', price: '2,800' },
          ],
        },
        {
          name: 'Soups',
          items: [
            { name: 'Egusi Soup', description: 'Melon seed soup with assorted meat and fish', price: '6,500' },
            { name: 'Efo Riro', description: 'Spinach soup with beef, tripe, and smoked fish', price: '6,000' },
            { name: 'Ogbono Soup', description: 'Wild mango seed soup with meat and fish', price: '7,000' },
            { name: 'Pepper Soup', description: 'Spicy clear soup with goat meat or catfish', price: '8,500', dietary: ['spicy'] },
            { name: 'Banga Soup', description: 'Palm fruit soup (Delta style)', price: '7,500' },
          ],
        },
        {
          name: 'Rice Dishes',
          items: [
            { name: 'Jollof Rice', description: 'Party-style one-pot rice with tomatoes and bell peppers', price: '4,500' },
            { name: 'Fried Rice', description: 'Nigerian fried rice with vegetables and liver', price: '5,000' },
            { name: 'Coconut Rice', description: 'Rice cooked in coconut milk with shrimps', price: '5,500' },
            { name: 'Tuwo Shinkafa', description: 'Rice pudding balls (northern delicacy)', price: '3,500' },
          ],
        },
        {
          name: 'Swallows & Proteins',
          items: [
            { name: 'Pounded Yam', description: 'Smooth pounded yam', price: '1,500' },
            { name: 'Amala', description: 'Yam flour pudding', price: '1,000' },
            { name: 'Eba', description: 'Cassava flakes pudding', price: '800' },
            { name: 'Semo', description: 'Semolina pudding', price: '1,000' },
            { name: 'Grilled Chicken', description: 'Half chicken marinated and grilled', price: '7,500' },
            { name: 'Suya Platter', description: 'Spicy grilled beef skewers with onions and tomatoes', price: '8,000', dietary: ['spicy'] },
            { name: 'Deep Fried Fish', description: 'Whole tilapia or catfish, seasoned and fried', price: '9,000' },
          ],
        },
      ],
    },
  ];

  const handleBookTable = (restaurant: Restaurant) => {
    setSelectedRestaurant(restaurant);
    setShowBookingModal(true);
  };

  return (
    <div className="overflow-hidden">
      {/* Hero Section */}
      <section className="relative pt-32 pb-24 lg:pt-40 lg:pb-32">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.pexels.com/photos-2609227/pexels-photo-2609227.jpeg?auto=compress&cs=tinysrgb&w=1920"
            alt="Restaurant dining"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-primary-950/95 to-primary-950/70" />
        </div>
        <div className="relative z-10 container-custom">
          <div className="max-w-3xl">
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">Dining</span>
            <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mt-3 mb-6">
              Culinary Excellence
            </h1>
            <p className="text-white/90 text-xl leading-relaxed">
              Three unique dining experiences under one roof. From authentic Chinese cuisine
              to intercontinental delicacies and traditional Nigerian dishes.
            </p>
          </div>
        </div>
      </section>

      {/* Restaurants Overview */}
      <section className="bg-white section-padding">
        <div className="container-custom">
          <div className="text-center mb-16">
            <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">Our Restaurants</span>
            <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3">
              Three Distinct Experiences
            </h2>
            <p className="text-primary-600 text-lg mt-4 max-w-2xl mx-auto">
              Each restaurant offers a unique culinary journey, curated by expert chefs to deliver unforgettable flavors.
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {restaurants.map((restaurant) => (
              <div
                key={restaurant.id}
                className="group relative bg-primary-50 rounded-xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500"
              >
                <div className="aspect-[4/3] overflow-hidden">
                  <img
                    src={restaurant.image}
                    alt={restaurant.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-primary-950/80 to-transparent" />
                </div>
                <div className="absolute top-4 left-4">
                  <span className="bg-warm-500 text-white text-xs font-medium px-3 py-1 rounded-full">
                    {restaurant.type}
                  </span>
                </div>
                <div className="absolute bottom-0 left-0 right-0 p-6">
                  <h3 className="font-serif text-xl font-semibold text-white mb-1">
                    {restaurant.name}
                  </h3>
                  <p className="text-warm-300 text-sm mb-2">{restaurant.tagline}</p>
                  <div className="flex items-center space-x-2 text-white/70 text-sm mb-4">
                    <Clock className="w-4 h-4" />
                    <span>{restaurant.hours}</span>
                  </div>
                  <button
                    onClick={() => handleBookTable(restaurant)}
                    className="w-full btn-primary text-sm"
                  >
                    Book a Table
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Restaurant Details with Menu */}
      {restaurants.map((restaurant, idx) => (
        <section
          key={restaurant.id}
          className={`section-padding ${idx % 2 === 0 ? 'bg-primary-50' : 'bg-white'}`}
        >
          <div className="container-custom">
            <div className="grid lg:grid-cols-2 gap-16 items-start">
              {/* Restaurant Info */}
              <div>
                <span className="text-warm-600 font-medium text-sm uppercase tracking-wider">
                  {restaurant.type}
                </span>
                <h2 className="font-serif text-4xl md:text-5xl font-bold text-primary-900 mt-3 mb-4">
                  {restaurant.name}
                </h2>
                <p className="text-warm-600 text-lg mb-6">{restaurant.tagline}</p>
                <p className="text-primary-600 leading-relaxed mb-8">{restaurant.description}</p>
                <div className="flex items-center space-x-4 mb-8">
                  <div className="flex items-center space-x-2 text-primary-700">
                    <Clock className="w-5 h-5 text-warm-500" />
                    <span>{restaurant.hours}</span>
                  </div>
                </div>
                <div className="flex items-center space-x-4 mb-6 text-primary-700">
                  <Phone className="w-5 h-5 text-warm-500" />
                  <span>+234 800 000 0000</span>
                </div>
                <button
                  onClick={() => handleBookTable(restaurant)}
                  className="btn-primary inline-flex items-center space-x-2"
                >
                  <span>Reserve a Table</span>
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>

              {/* Menu */}
              <div>
                <div className="bg-white rounded-xl shadow-lg overflow-hidden">
                  {/* Menu Tabs */}
                  <div className="flex overflow-x-auto border-b border-primary-100">
                    {restaurant.menu.map((category, catIdx) => (
                      <button
                        key={catIdx}
                        onClick={() => setSelectedTab(catIdx)}
                        className={`px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors ${
                          selectedTab === catIdx
                            ? 'bg-warm-500 text-white'
                            : 'text-primary-600 hover:bg-primary-50'
                        }`}
                      >
                        {category.name}
                      </button>
                    ))}
                  </div>

                  {/* Menu Items */}
                  <div className="p-6">
                    <div className="space-y-4">
                      {restaurant.menu[selectedTab]?.items.map((item, itemIdx) => (
                        <div
                          key={itemIdx}
                          className="flex justify-between items-start py-4 border-b border-primary-50 last:border-0"
                        >
                          <div className="flex-1 pr-4">
                            <div className="flex items-center gap-2 mb-1">
                              <h4 className="font-medium text-primary-900">{item.name}</h4>
                              {item.dietary?.map((tag, tagIdx) => (
                                <span
                                  key={tagIdx}
                                  className={`text-xs px-2 py-0.5 rounded ${
                                    tag === 'vegetarian'
                                      ? 'bg-green-100 text-green-700'
                                      : tag === 'spicy'
                                      ? 'bg-red-100 text-red-700'
                                      : 'bg-primary-100 text-primary-700'
                                  }`}
                                >
                                  {tag === 'vegetarian' && <Leaf className="w-3 h-3 inline mr-1" />}
                                  {tag === 'spicy' && <Flame className="w-3 h-3 inline mr-1" />}
                                  {tag}
                                </span>
                              ))}
                            </div>
                            <p className="text-sm text-primary-500">{item.description}</p>
                          </div>
                          <div className="text-right">
                            <span className="font-semibold text-primary-900">₦{item.price}</span>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      ))}

      {/* Booking Modal */}
      {showBookingModal && selectedRestaurant && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary-950/80">
          <div className="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div className="relative h-48">
              <img
                src={selectedRestaurant.image}
                alt={selectedRestaurant.name}
                className="w-full h-full object-cover"
              />
              <div className="absolute inset-0 bg-primary-950/50" />
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="text-center">
                  <h3 className="font-serif text-2xl font-bold text-white">{selectedRestaurant.name}</h3>
                  <p className="text-warm-300">{selectedRestaurant.tagline}</p>
                </div>
              </div>
            </div>
            <div className="p-6 space-y-6">
              <form className="space-y-4">
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">Date</label>
                    <input
                      type="date"
                      className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">Time</label>
                    <select className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none">
                      <option>12:00 PM</option>
                      <option>1:00 PM</option>
                      <option>2:00 PM</option>
                      <option>6:00 PM</option>
                      <option>7:00 PM</option>
                      <option>8:00 PM</option>
                      <option>9:00 PM</option>
                    </select>
                  </div>
                </div>
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">Number of Guests</label>
                    <div className="relative">
                      <Users className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-primary-400" />
                      <select className="w-full pl-10 pr-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none appearance-none">
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((num) => (
                          <option key={num} value={num}>
                            {num} {num === 1 ? 'Guest' : 'Guests'}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-primary-700 block mb-1">Occasion</label>
                    <select className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none">
                      <option>None</option>
                      <option>Birthday</option>
                      <option>Anniversary</option>
                      <option>Business Dinner</option>
                      <option>Date Night</option>
                      <option>Other</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Name</label>
                  <input
                    type="text"
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    placeholder="Your full name"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Email</label>
                  <input
                    type="email"
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    placeholder="your@email.com"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Phone</label>
                  <input
                    type="tel"
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none"
                    placeholder="+234 800 000 0000"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium text-primary-700 block mb-1">Special Requests</label>
                  <textarea
                    className="w-full px-4 py-2 border border-primary-200 rounded-lg focus:ring-2 focus:ring-warm-500 focus:border-warm-500 outline-none resize-none"
                    rows={3}
                    placeholder="Any dietary requirements or special requests..."
                  />
                </div>
              </form>
            </div>
            <div className="p-6 border-t border-primary-100 flex gap-4">
              <button
                onClick={() => setShowBookingModal(false)}
                className="flex-1 btn-secondary"
              >
                Cancel
              </button>
              <button
                onClick={() => {
                  alert('Reservation request submitted! (This is a demo)');
                  setShowBookingModal(false);
                }}
                className="flex-1 btn-primary"
              >
                Confirm Reservation
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Restaurant;
