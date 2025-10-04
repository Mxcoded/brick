<style>
:root {
    --primary: #4361ee;
    --secondary: #3f37c9;
    --accent: #4cc9f0;
    --light: #f8f9fa;
    --dark: #212529;
    --success: #4caf50;
    --warning: #ff9800;
    --danger: #f44336;
}

  body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .form-container {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      margin: 2rem auto;
      max-width: auto;
  }

  .form-header {
      background: linear-gradient(120deg, var(--primary), var(--secondary));
      color: white;
      padding: 2.5rem 2rem;
      text-align: center;
  }

  .form-header h1 {
      font-weight: 700;
      margin-bottom: 0.5rem;
      font-size: 2.2rem;
  }

  .form-header p {
      opacity: 0.9;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
  }

  .form-body {
      padding: 2.5rem;
  }

  .section-title {
      color: var(--secondary);
      border-bottom: 2px solid var(--accent);
      padding-bottom: 0.75rem;
      margin-bottom: 1.5rem;
      font-weight: 600;
      position: relative;
  }

  .section-title:after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 60px;
      height: 2px;
      background: var(--primary);
  }

  .form-card {
      background: #f8fbff;
      border-radius: 12px;
      border: 1px solid #e1e8f0;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: all 0.3s ease;
  }

  .form-card:hover {
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
      border-color: #c2d5ff;
  }

  .form-label {
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 0.5rem;
  }

  .required:after {
      content: " *";
      color: var(--danger);
  }

  .btn-primary {
      background: linear-gradient(120deg, var(--primary), var(--secondary));
      border: none;
      padding: 0.75rem 2rem;
      font-weight: 600;
      transition: all 0.3s ease;
  }

  .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
  }

  .form-check-input:checked {
      background-color: var(--primary);
      border-color: var(--primary);
  }

  .hidden {
      display: none;
  }

  .toggle-container {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
  }

  .toggle-option {
      flex: 1;
      text-align: center;
      padding: 1rem;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
  }

  .toggle-option.active {
      border-color: var(--primary);
      background-color: rgba(67, 97, 238, 0.05);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
  }

  .toggle-option i {
      font-size: 1.75rem;
      margin-bottom: 0.75rem;
      color: var(--primary);
  }

  .member-card {
      background: white;
      border-radius: 12px;
      border: 1px solid #e1e8f0;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
  }

  .member-header {
      display: flex;
      align-items: center;
      margin-bottom: 1.25rem;
  }

  .member-icon {
      width: 40px;
      height: 40px;
      background: var(--accent);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      margin-right: 1rem;
      font-size: 1.25rem;
  }

  .terms-container {
      background: #f8fbff;
      border-radius: 12px;
      padding: 1.5rem;
      margin-top: 1rem;
  }

  .form-footer {
      text-align: center;
      padding-top: 1.5rem;
      border-top: 1px solid #e9ecef;
      margin-top: 1rem;
      color: #6c757d;
  }

  @media (max-width: 768px) {
      .form-body {
          padding: 1.5rem;
      }

      .toggle-container {
          flex-direction: column;
      }

  }

  .nav-pills .nav-link.active {
      background-color: gold !important;
      color: black;
  }
  
</style>