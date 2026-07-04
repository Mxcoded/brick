import { useState } from 'react';
import { X, ChevronLeft, ChevronRight } from 'lucide-react';

interface GalleryImage {
  src: string;
  alt: string;
  category: string;
}

const Gallery = () => {
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  const categories = [
    { id: 'all', label: 'All' },
    { id: 'rooms', label: 'Rooms & Suites' },
    { id: 'restaurant', label: 'Restaurant' },
    { id: 'pool', label: 'Pool & Spa' },
    { id: 'facilities', label: 'Facilities' },
    { id: 'events', label: 'Events' },
  ];

  const images: GalleryImage[] = [
    { src: 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Luxury suite', category: 'rooms' },
    { src: 'https://images.pexels.com/photos/1579253/pexels-photo-1579253.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Presidential suite', category: 'rooms' },
    { src: 'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Hotel room', category: 'rooms' },
    { src: 'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Classic room', category: 'rooms' },
    { src: 'https://images.pexels.com/photos-2609227/pexels-photo-2609227.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Restaurant dining', category: 'restaurant' },
    { src: 'https://images.pexels.com/photos-2869275/pexels-photo-2869275.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Fine dining', category: 'restaurant' },
    { src: 'https://images.pexels.com/photos/2290071/pexels-photo-2290071.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Food presentation', category: 'restaurant' },
    { src: 'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Swimming pool', category: 'pool' },
    { src: 'https://images.pexels.com/photos/3429265/pexels-photo-3429265.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Spa treatment room', category: 'pool' },
    { src: 'https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Poolside', category: 'pool' },
    { src: 'https://images.pexels.com/photos/1117650/pexels-photo-1117650.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Conference room', category: 'facilities' },
    { src: 'https://images.pexels.com/photos/2609286/pexels-photo-2609286.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Gym', category: 'facilities' },
    { src: 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Hotel exterior', category: 'facilities' },
    { src: 'https://images.pexels.com/photos/2343475/pexels-photo-2343475.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Lounge area', category: 'facilities' },
    { src: 'https://images.pexels.com/photos/1125136/pexels-photo-1125136.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Event hall', category: 'events' },
    { src: 'https://images.pexels.com/photos/587741/pexels-photo-587741.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Wedding setup', category: 'events' },
    { src: 'https://images.pexels.com/photos/2291462/pexels-photo-2291462.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Meeting setup', category: 'events' },
    { src: 'https://images.pexels.com/photos/2606837/pexels-photo-2606837.jpeg?auto=compress&cs=tinysrgb&w=800', alt: 'Room service', category: 'rooms' },
  ];

  const filteredImages = selectedCategory === 'all'
    ? images
    : images.filter(img => img.category === selectedCategory);

  const openLightbox = (index: number) => {
    setCurrentImageIndex(index);
    setLightboxOpen(true);
  };

  const closeLightbox = () => {
    setLightboxOpen(false);
  };

  const navigatePrev = () => {
    setCurrentImageIndex((prev) =>
      prev === 0 ? filteredImages.length - 1 : prev - 1
    );
  };

  const navigateNext = () => {
    setCurrentImageIndex((prev) =>
      prev === filteredImages.length - 1 ? 0 : prev + 1
    );
  };

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
            <span className="text-warm-500 font-medium text-sm uppercase tracking-wider">Gallery</span>
            <h1 className="font-serif text-5xl md:text-6xl font-bold text-white mt-3 mb-6">
              Explore Our Spaces
            </h1>
            <p className="text-white/90 text-xl leading-relaxed">
              Take a visual tour through our luxurious rooms, fine dining restaurants,
              refreshing pool, and elegant event spaces.
            </p>
          </div>
        </div>
      </section>

      {/* Category Filter */}
      <section className="bg-white py-12 border-b border-primary-100">
        <div className="container-custom">
          <div className="flex flex-wrap justify-center gap-4">
            {categories.map((category) => (
              <button
                key={category.id}
                onClick={() => setSelectedCategory(category.id)}
                className={`px-6 py-3 rounded-full text-sm font-medium transition-all duration-300 ${
                  selectedCategory === category.id
                    ? 'bg-primary-900 text-white'
                    : 'bg-primary-50 text-primary-700 hover:bg-primary-100'
                }`}
              >
                {category.label}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Gallery Grid */}
      <section className="bg-primary-50 section-padding">
        <div className="container-custom">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {filteredImages.map((image, index) => (
              <div
                key={index}
                className="group relative cursor-pointer overflow-hidden rounded-lg aspect-[4/3]"
                onClick={() => openLightbox(index)}
              >
                <img
                  src={image.src}
                  alt={image.alt}
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
                <div className="absolute inset-0 bg-primary-950/0 group-hover:bg-primary-950/50 transition-all duration-300" />
                <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                  <div className="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                    <div className="w-6 h-6 bg-white rounded-full" />
                  </div>
                </div>
                <div className="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-primary-950/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                  <p className="text-white text-sm font-medium">{image.alt}</p>
                </div>
              </div>
            ))}
          </div>

          {filteredImages.length === 0 && (
            <div className="text-center py-20">
              <p className="text-primary-600 text-lg">No images found in this category.</p>
            </div>
          )}
        </div>
      </section>

      {/* Lightbox */}
      {lightboxOpen && (
        <div className="fixed inset-0 z-50 bg-primary-950/95 flex items-center justify-center">
          {/* Close Button */}
          <button
            onClick={closeLightbox}
            className="absolute top-6 right-6 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors duration-300 z-10"
          >
            <X className="w-6 h-6 text-white" />
          </button>

          {/* Navigation */}
          <button
            onClick={navigatePrev}
            className="absolute left-6 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors duration-300"
          >
            <ChevronLeft className="w-6 h-6 text-white" />
          </button>
          <button
            onClick={navigateNext}
            className="absolute right-6 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-colors duration-300"
          >
            <ChevronRight className="w-6 h-6 text-white" />
          </button>

          {/* Image */}
          <div className="max-w-5xl max-h-[80vh] mx-auto px-16">
            <img
              src={filteredImages[currentImageIndex]?.src}
              alt={filteredImages[currentImageIndex]?.alt}
              className="max-w-full max-h-[80vh] object-contain rounded-lg"
            />
            <p className="text-center text-white/80 mt-4">
              {filteredImages[currentImageIndex]?.alt}
            </p>
          </div>

          {/* Counter */}
          <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 text-white/60 text-sm">
            {currentImageIndex + 1} / {filteredImages.length}
          </div>
        </div>
      )}
    </div>
  );
};

export default Gallery;
