# Showroom ERP – Implementation Plan (Final)

A full-featured showroom ERP on **Laravel 12** — **API-first** architecture, custom Blade + Bootstrap 5.3 now, React-ready tomorrow. Fully responsive.

---

## Architecture Philosophy

```
┌──────────────────────────────────────────────────┐
│              Laravel 12 Backend                   │
│                                                   │
│  ┌──────────────┐    ┌────────────────────────┐  │
│  │  REST API     │    │  Service Layer          │  │
│  │  /api/v1/...  │    │  (SaleService,           │  │
│  │  Sanctum Auth │    │   PurchaseService, etc.) │  │
│  └──────┬───────┘    └────────────┬───────────┘  │
│         │                         │               │
│  ┌──────▼─────────────────────────▼───────────┐  │
│  │            Eloquent Models + DB             │  │
│  └─────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────┘
         │                        │
         ▼                        ▼
┌─────────────────┐    ┌──────────────────────────┐
│  NOW (Phase 1)  │    │  FUTURE (Phase 2)         │
│  Blade + jQuery │    │  React / Next.js SPA      │
│  Bootstrap 5.3  │    │  Same /api/v1 endpoints   │
│  Fully responsive│   │  No backend change needed │
└─────────────────┘    └──────────────────────────┘
```

> [!IMPORTANT]
> **API-First Design**: All business logic lives in **Service classes** and is exposed through **versioned REST API routes** (`/api/v1/`). Blade views call these same APIs via jQuery AJAX. When you add React later, you just swap the frontend — zero backend changes.

---

## ❌ What We Drop / Avoid

| Dropped | Reason |
|---|---|
| Filament | Requires Tailwind + Node |
| Laravel Breeze/Jetstream | Requires Node/Vite |
| npm / Vite | Not needed |
| Tailwind CSS | Not wanted |

---

## ✅ Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 |
| API Auth | **Laravel Sanctum** (token-based, SPA-ready) |
| Roles | **Spatie Laravel-Permission** |
| Barcodes | milon/barcode |
| PDF | barryvdh/laravel-dompdf |
| Current UI | **Bootstrap 5.3 CDN** (fully responsive) |
| Icons | Bootstrap Icons CDN |
| Charts | Chart.js CDN |
| Interactivity | **jQuery 3.7 CDN** |
| Future UI | React / Next.js (via same `/api/v1` — no changes needed) |

> [!NOTE]
> Zero `npm install`. All CSS/JS from CDN only.

---

## User Review Required

> [!IMPORTANT]
> **MySQL DB**: Ensure `laravel_showroom` database exists. I'll also fix the leading-space bug on `DB_*` lines in [.env](file:///d:/Documents/laravel/showroom/showroom/.env).

---

## Proposed Changes

### 1. Packages (composer only)

```bash
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require milon/barcode
composer require barryvdh/laravel-dompdf
```

---

### 2. Database Migrations (15 tables)

In dependency order:

| Table | Key Columns |
|---|---|
| `branches` | name, city, address, phone, is_active |
| `categories` | name, parent_id (self-ref), type ENUM(vehicle,part) |
| `brands` | name, logo |
| `vehicle_models` | brand_id, name, year |
| `products` | name, sku, barcode, category_id, type, brand_id, model_id, purchase_price, selling_price, description, image |
| `inventories` | product_id, branch_id, quantity, min_quantity |
| `stock_movements` | product_id, branch_id, type ENUM(in,out,adjustment), quantity, reference_type, reference_id, notes, created_by |
| `customers` | name, phone, email, address, type ENUM(individual,company) |
| `suppliers` | name, phone, email, address, company_name |
| `sales` | customer_id, branch_id, invoice_no (auto), subtotal, discount, tax, total, amount_paid, payment_status, notes, sold_by |
| `sale_items` | sale_id, product_id, quantity, unit_price, **cost_price**, discount, total |
| `purchases` | supplier_id, branch_id, reference_no, subtotal, total, status, notes |
| `purchase_items` | purchase_id, product_id, quantity, cost_price, total |
| `expenses` | title, category, amount, branch_id, date, notes, created_by |
| `payments` | sale_id, amount, method ENUM(cash,card,transfer,cheque), reference, notes |

---

### 3. Models

`app/Models/`: Branch, Category, Brand, VehicleModel, Product, Inventory, StockMovement, Customer, Supplier, Sale, SaleItem, Purchase, PurchaseItem, Expense, Payment

User model extended with `HasRoles` + `HasApiTokens`.

---

### 4. Service Classes (business logic lives here only)

```
app/Services/
├── SaleService.php        # create sale + items + reduce inventory + stock_movement(out)
├── PurchaseService.php    # create purchase + items + increase inventory + stock_movement(in)
└── ProfitService.php      # P&L calculation (Revenue - COGS - Expenses)
```

---

### 5. REST API (`routes/api.php` – `/api/v1/`)

All endpoints are JSON, Sanctum-protected, versioned.

| Resource | Endpoints |
|---|---|
| Auth | `POST /login`, `POST /logout` |
| Branches | Full CRUD |
| Categories | Full CRUD |
| Brands + Models | Full CRUD |
| Products | CRUD + `GET /products/barcode/{code}` (barcode lookup) |
| Inventory | Index per branch, adjust stock |
| Stock Movements | Index (read-only) |
| Customers + Suppliers | Full CRUD |
| Sales | CRUD + `GET /sales/{id}/invoice` (PDF) |
| Purchases | Full CRUD |
| Expenses | Full CRUD |
| Payments | Full CRUD |
| Reports | `GET /reports/profit-loss?from=&to=&branch_id=` |
| Dashboard | `GET /dashboard/stats` |

> [!TIP]
> These **same endpoints** are what a future React SPA will call. No changes needed on the backend.

---

### 6. Blade Admin Panel (Phase 1 frontend)

#### Layout (`resources/views/layouts/admin.blade.php`)
- Responsive Bootstrap 5.3 sidebar (collapses to hamburger on mobile)
- All CSS/JS from CDN only — no `npm`, no `vite`
- `@yield('content')` + `@stack('scripts')`

#### Responsive Design Principles
- Bootstrap grid (`col-lg-3 col-md-6 col-12`) on all cards
- Responsive DataTables (CDN) — horizontal scroll on mobile
- Modals for inline actions (add item, record payment)
- Sidebar collapses via Bootstrap offcanvas on mobile

#### Controllers (`app/Http/Controllers/Admin/`)
- Thin controllers — call Service classes, delegate to API internally
- Return Blade views (index, create, edit, show)
- Views consume `/api/v1` via jQuery AJAX for dynamic parts (cart, barcode scan)

#### Dashboard
- Stat cards: Revenue, COGS, Net Profit, Sales Count (responsive grid)
- Chart.js line chart: 7-day sales
- Chart.js doughnut: sales by category
- Low-stock alert table
- Recent sales table

---

### 7. Roles & Permissions (Spatie)

| Role | Permissions |
|---|---|
| Admin | All modules + users + settings |
| Salesperson | Products(view), Customers(CRU), Sales(C+view), Payments |
| Accountant | Sales(view), Purchases(view), Expenses(CRUD), Reports |

Applied via `middleware('role:admin')` on routes and `@can` in Blade.

---

## Future React Migration (Zero Backend Work)

When ready to add React:
1. `npx create-react-app` or `npm create vite@latest`
2. Point it at `your-domain.com/api/v1`
3. Use Sanctum SPA auth (already configured)
4. All API endpoints already exist ✅

---

## Verification Plan

```bash
php artisan migrate:fresh --seed
php artisan serve
```

1. `http://localhost:8000/login` → login
2. Dashboard: all widgets load
3. Create Branch → Category → Brand → Product (with barcode)
4. Purchase: add stock, verify inventory + stock_movement(in)
5. Sale: scan barcode, add to cart (jQuery AJAX), submit
6. Inventory drops, stock_movement(out) logged
7. P&L report shows correct figures
8. PDF invoice downloads
9. Mobile: sidebar collapses, all tables scroll horizontally ✅
10. Salesperson login: Expenses hidden ✅
