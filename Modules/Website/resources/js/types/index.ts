export interface User {
  id: string;
  name: string;
  email: string;
  avatar: string;
  totalInvested: number;
  activeProjects: number;
  totalReturns: number;
}

export interface PageProps {
  auth: {
    user: User | null; // Account for guests safely
  };
  [key: string]: any; // Allow for additional props without TypeScript errors
}