import type { ReactNode } from 'react';
import Navbar from '../Components/Navbar';
import Footer from '../Components/Footer';

interface IMainLayout {
    children: ReactNode;
}
export default function MainLayout({ children }: IMainLayout) {
  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />
      <main className="flex-1">
        {children}
      </main>
      <Footer />
    </div>
  );
};
