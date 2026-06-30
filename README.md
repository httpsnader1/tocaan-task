# Order & Payment Management API

A Laravel REST API for managing orders and payments, built with clean code and
**extensibility** in mind: new payment gateways can be added with minimal changes
using a strategy pattern driven by configuration.

## Features

- **Orders** – create (with automatic total calculation), update, delete, list (with
  status filtering and pagination), and view a single order.
- **Payments** – process a payment for a confirmed order through a pluggable gateway,
  with stock decremented on success.
- **Pluggable gateways** – add a new gateway by writing one class and adding one line
  of config. No changes to controllers/services required.
- **JWT authentication** – register, login, profile, logout.
- **Validation** – every input is validated with meaningful error messages.
- **Tested** – feature and unit tests, including the payment gateway logic.

## Tech Stack

- PHP 8.2+ / Laravel 12
- [`tymon/jwt-auth`](https://github.com/tymondesigns/jwt-auth) for JWT authentication
- [`lorisleiva/laravel-actions`](https://laravelactions.com/) for single-action endpoints
- MySQL (app) / SQLite in-memory (tests)

---

## Setup

```bash
# 1. Install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure the database in .env, then:
#    DB_DATABASE=tocaan_task   DB_USERNAME=root   DB_PASSWORD=

# 4. Generate the JWT signing secret (writes JWT_SECRET to .env)
php artisan jwt:secret

# 5. Run migrations and seed demo data (10 users + products)
php artisan migrate --seed

# 6. Serve
php artisan serve
```

The API is now available at `http://127.0.0.1:8000/api`.

### Seeded login

| Email | Password |
| --- | --- |
| `tocaan.task1@gmail.com` | `12345678` |

(Users `tocaan.task1` … `tocaan.task10` are seeded.)

---

## Authentication

All `products`, `orders`, and `payments` endpoints require a JWT.

1. `POST /api/auth/register` or `POST /api/auth/login` → returns a `token`.
2. Send it on every protected request:

```
Authorization: Bearer <token>
```

---

## Endpoints

### Auth
| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/api/auth/register` | Register and receive a token |
| POST | `/api/auth/login` | Login and receive a token |
| GET | `/api/auth/profile` | Current user (auth) |
| GET | `/api/auth/logout` | Invalidate the token (auth) |

### Orders

Every order endpoint is **scoped to the authenticated user** — a user can only list,
view, update, delete, or pay for their own orders. The listing is filtered by the
current user, and each single-order action guards ownership explicitly, returning `403`
when the order belongs to someone else.

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/orders` | List the current user's orders (paginated, filterable) |
| POST | `/api/orders` | Create an order |
| GET | `/api/orders/{order}` | View one of the user's orders |
| PATCH | `/api/orders/{order}` | Update an order |
| DELETE | `/api/orders/{order}` | Delete an order |
| GET | `/api/orders/{order}/confirm` | Mark the order as `confirmed` (testing helper — see note) |
| POST | `/api/orders/{order}/pay` | Process a payment for the order |

> **Note on `confirm`:** payments can only be processed for orders in the `confirmed`
> status. In a real system that transition would be driven by business logic (e.g. an
> admin approval or an automated workflow). This endpoint is provided purely as a
> **testing convenience** so the payment flow can be exercised end-to-end via the API /
> Postman collection.

### Filtering & pagination

List endpoints are paginated (10 per page). Orders support query filters:

```
GET /api/orders?filterStatus=confirmed
GET /api/orders?filterPaymentMethod=credit_card
GET /api/orders?filterPaymentStatus=success
GET /api/orders?page=2
```

### Example: create an order

```http
POST /api/orders
Authorization: Bearer <token>

{
  "products": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

The `total` is calculated server-side from each product's current price — it is never
trusted from the client.

### Example: pay for an order

```http
POST /api/orders/{order}/pay
Authorization: Bearer <token>

{ "payment_method": "credit_card" }
```

---

## Payment Gateway Extensibility

Payment gateways use a **strategy pattern** resolved through configuration, so the
system is open for extension but closed for modification.

### How it works

1. `config/payments.php` maps a method name → a gateway class:

   ```php
   'gateways' => [
       'credit_card' => \App\Services\Payments\CreditCardService::class,
       'apple_pay'   => \App\Services\Payments\ApplePayService::class,
   ],
   ```

2. The incoming `payment_method` is validated against `App\Enums\PaymentMethodEnum`, so
   only supported methods reach the service — an unknown value returns a `422` with a
   clear validation message before any processing happens.

3. `PaymentService::resolvePaymentMethod()` looks up the requested method and resolves
   the class from the container.

4. The resolved gateway's `process(Order $order)` runs the gateway logic and returns a
   normalized response (`status`, `transaction_id`, `data`). `PaymentService` then
   persists the `Payment` record and, on success, decrements stock.

Adding a gateway requires **no changes** to controllers, the `PaymentService`, the
routes, or the database — only a new gateway class, one config line, and one enum case.

### Adding a new gateway (e.g. PayPal)

1. Add the case to `App\Enums\PaymentMethodEnum` — required first, since `payment_method`
   is validated against this enum and the config below references it:

   ```php
   enum PaymentMethodEnum: string
   {
       // ...
       case PAYPAL = 'paypal';
       
       
       public function text(): string
       {
           return match ($this) {
               // ...
               self::PAYPAL => 'Paypal',
           };
       }
   }
   ```

2. Create the gateway service:

   ```php
   // app/Services/Payments/PayPalService.php
   namespace App\Services\Payments;

   use App\Models\Order;
   use Lorisleiva\Actions\Concerns\AsObject;

   class PayPalService
   {
       use AsObject;

       public function process(Order $order): array
       {
           // Call the PayPal SDK / API here using credentials from config.
           return PaymentService::make()->paymentResponse(
               status: true,
               transactionID: 'PAYPAL-' . uniqid('', true),
               data: ['orderID' => $order->id],
           );
       }
   }
   ```

3. Register it in `config/payments.php`:

   ```php
   use App\Enums\PaymentMethodEnum;

   'gateways' => [
       // ...
       PaymentMethodEnum::PAYPAL->value => \App\Services\Payments\PayPalService::class,
   ],

   'paypal' => [
       // SOON , ADD DATA FOR PAYPAL CREDENTIALS
   ],
   ```

That's it — clients can now send `"payment_method": "paypal"`.

### Gateway configuration (API keys / secrets)

Credentials live in `config/payments.php` and are read from `.env`, so secrets stay out
of source control:

```php
'credit_card' => [
    'api_key' => env('CREDIT_CARD_API_KEY'),
],
```

---

## Business Rules

- Orders are private: a user can only access their own orders. The index filters by the
  authenticated user, and `show` / `update` / `delete` / `pay` each guard ownership via
  `OrderService::checkOrderOwnership()` (returns `403` otherwise).
- A payment can only be processed for an order in the **confirmed** status.
- An order **cannot be deleted or updated** once it has a **successful** payment.
- Order totals are always computed server-side from product prices.
- A product cannot be ordered beyond its available stock; stock is decremented when a
  payment succeeds.

---

## Testing

The suite uses an in-memory SQLite database (configured in `phpunit.xml`).

```bash
php artisan test
```

Coverage includes:

- **Auth** – registration, unique-email, login success/failure, protected routes.
- **Orders** – creation & total calculation, validation, stock checks, status filtering,
  pagination, update, delete, per-user scoping (a user cannot see/delete another user's
  orders), and the "cannot modify/delete an order with a successful payment" rule.
- **Payments** – payment on a confirmed order, stock decrement, the "confirmed-only"
  rule, unsupported gateway rejection, double-payment prevention, and per-gateway logic.
- **Unit** – gateway resolution (strategy pattern) and each gateway's `process()`.

---

## API Documentation

A ready-to-import collection is included at the project root:

- `Tocaan Task.postman.json` (Postman)
- `Tocaan Task.apidog.json` (Apidog)

Import it, set the `token` after logging in, and the requests are organized by
Authentication, Orders, Products, and Payments.

---

## Notes & Assumptions

- The "cannot delete an order with payments" rule is interpreted as **successful**
  payments — an order with only a failed/pending payment can still be deleted/updated.
- Order status transitions (e.g. moving an order to `confirmed`) are assumed to be
  handled administratively/by seed; payment intentionally requires a confirmed order.
- Gateway `process()` methods are simulated (they always return success) and are the
  single place to plug in a real SDK call.
- The default guard for the API is `api` (JWT); the web guard is unused.
