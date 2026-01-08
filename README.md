# Brickspoint HMS - Modular Hospitality Management System

**Brick** is a comprehensive, modular Enterprise Resource Planning (ERP) system designed specifically for the hospitality industry. Built on **Laravel 11**, it utilizes a Modular Monolith architecture to manage every aspect of a hotel operations, from front-desk reservations and housekeeping to restaurant POS, gym management, and staff HR.

## 🚀 Key Features & Modules

The application is split into distinct modules using `nwidart/laravel-modules`, allowing for independent scaling and maintenance of specific business features.

### 🏨 Core Hotel Operations

* **Frontdesk CRM:**
* **Guest Management:** Detailed guest profiles, preferences, and types (VIP, Regular, Corporate).
* **Registrations:** Handles Walk-ins, Check-ins, and Finalizing Registrations.
* **Billing:** Supports various billing types and checkout details.
* **Booking Sources:** Track where guests are coming from (OTA, Direct, Agent).



### 🍽️ Food & Beverage

* **Restaurant:**
* **POS System:** Manages Orders, Order Items, and Table assignments.
* **Menu Management:** Categories and Menu Items with image support.
* **Waiter Dashboard:** Specific views for staff to manage active tables.


* **Banquet & Events:**
* **Event Planning:** Manages event days, setups, and location times.
* **Function Sheets:** Generates PDF Function Sheets and Invoices.
* **Menus:** specialized menu selection for large events.



### 🏋️ Amenities & Services

* **Gym:**
* **Membership Management:** specific database logic for members and membership types.
* **Trainers:** Assign trainers to members and track trainer payments.
* **Health Tracking:** Logs health and fitness fields for members.



### 🏢 Operations & Logistics

* **Inventory:**
* **Stock Control:** Multi-store support, Restock Logs, and Usage Logs.
* **Suppliers:** Management of external vendors and price history.
* **Departments:** Internal distribution of inventory.


* **Maintenance:**
* **Issue Tracking:** Log maintenance requests and track resolution status.


* **Tasks:**
* **Project Management:** Assign tasks to users, track updates, and status changes.



### 👥 Human Resources

* **Staff:**
* **Employee Records:** Comprehensive data including educational background, employment history, NIN/BVN data.
* **Leave Management:** Leave balances, requests, and email notifications for approvals.



### 🌐 Online Presence

* **Website (CMS):**
* **Frontend:** Manages the public-facing hotel website including "About Us", "Rooms", and "Contact" pages.
* **Booking Engine:** Allows guests to search rooms and make bookings online.
* **Content Management:** Admin control for Testimonials, Amenities, and Room Images.



### ⚙️ Administration

* **Admin:** Central dashboard for system-wide configuration.
* **Auth:** Custom authentication logic including Role-Based Access Control (RBAC).

---

## 🛠️ Tech Stack

* **Framework:** Laravel 11.x
* **Language:** PHP 8.2+
* **Database:** MySQL
* **Frontend:** Blade Templates, Vite, Bootstrap (inferred from CSS/JS structure).
* **Key Packages:**
* `nwidart/laravel-modules`: For modular architecture.
* `spatie/laravel-permission`: For Roles and Permissions.
* `yajra/laravel-datatables`: For advanced data tables.
* `barryvdh/laravel-dompdf`: For generating PDF invoices and reports.



---

## 💻 Installation

1. **Clone the repository**
```bash
git clone https://github.com/mxcoded/brick.git
cd brick

```


2. **Install Dependencies**
```bash
composer install
npm install

```


3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate

```


*Configure your database credentials in the `.env` file.*
4. **Database Migration & Seeding**
Since this is a modular application, migrations must run for both the core and the modules.
```bash
php artisan migrate
# It is recommended to run module migrations if not covered by the main command
php artisan module:migrate 

# Seed the database (creates default Roles, Admin, Settings)
php artisan db:seed

```


5. **Compile Assets**
```bash
npm run build

```


6. **Run the Application**
```bash
php artisan serve

```



---

## 📂 Architecture: Modular Monolith

This project does not follow the standard Laravel folder structure strictly. Instead, business logic is encapsulated in the `Modules/` directory.

**Directory Structure:**

```
/Modules
  /Admin          # System Administration
  /Banquet        # Event & Catering Logic
  /Frontdeskcrm   # Reception & Guest Logic
  /Gym            # Fitness Center Logic
  /Inventory      # Stock & Supply Chain
  /Maintenance    # Repairs & Operations
  /Restaurant     # Food Service & POS
  /Staff          # HR & Payroll
  /Tasks          # Internal Task Tracking
  /Website        # Public Front-end CMS
/app              # Core Application Shared Logic (User, Auth)
/database         # Core Migrations
/public           # Compiled Assets

```

Each module operates like a mini-Laravel application with its own `Routes`, `Controllers`, `Models`, `Views`, and `Migrations`.

---

## 📝 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). Please check the project root for specific licensing regarding the "Brick" application logic.