import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import React from 'react'; 
import MainLayout from './Layouts/MainLayout';

createInertiaApp({
    
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.{jsx,tsx}', {
      eager: true,
    });

    const page =
      pages[`./Pages/${name}.tsx`] ||
      pages[`./Pages/${name}.jsx`] ||
      pages[`./Pages/${name}/index.tsx`] ||
      pages[`./Pages/${name}/index.jsx`];

    if (!page) {
      throw new Error(`Page not found: ${name}`);
    }

    const component = (page as any).default;

    if (!component) {
      throw new Error(`Component default export missing for: ${name}`);
    }

    if (!component.layout) {

      // FIXED: Removed the object curly braces and wrapped with valid JSX elements
      if(name.startsWith('Admin/')){
        component.layout = (pageNode: React.ReactNode) => (<>{pageNode}</>);
      }else {
        component.layout = (pageNode: React.ReactNode) => (<MainLayout>{pageNode}</MainLayout>);
      }
    }

    return component;
  },

  setup({ el, App, props }) {
    createRoot(el).render(
        <App {...props} />
    );
  },
});
