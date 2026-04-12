# Skills & Knowledge Map — Micro-SaaS Invoice Generator

> ทักษะที่จำเป็นสำหรับพัฒนาโปรเจคนี้ แบ่งตาม layer และ priority

---

## 1. Backend (Laravel 11 / PHP)

### Must-have
| ทักษะ | ใช้ที่ไหน |
|---|---|
| Laravel 11 fundamentals (routing, middleware, controllers) | ทุกส่วนของ app |
| Eloquent ORM + migrations | models: Invoice, Client, Product, Company |
| Laravel Queues + Jobs | `GenerateInvoicePdfJob` |
| Laravel Horizon | monitor queue `pdf`, `emails`, `default` |
| Laravel Sanctum (SPA auth) | session-based auth สำหรับ Inertia |
| Laravel Socialite | Google OAuth login |
| Laravel Breeze + Inertia starter | auth scaffolding |
| Laravel Scout | search clients/products |
| Form Request validation | `StoreInvoiceRequest` |
| Service classes (thin controller pattern) | `TaxEngineService`, `InvoicePdfService`, `AiAutofillService` |
| Laravel Scheduling | auto-update overdue invoices ทุกเช้า |
| Spatie Media Library | logo upload ไป R2 |
| Spatie Browsershot | HTML → PDF via Puppeteer |

### Nice-to-have
- PHPStan static analysis (level 6+)
- Laravel Telescope (local debug)
- Laravel Pulse (production monitoring)

---

## 2. Frontend (Vue 3 + Inertia.js)

### Must-have
| ทักษะ | ใช้ที่ไหน |
|---|---|
| Vue 3 Composition API + `<script setup>` | ทุก component |
| Inertia.js (SPA on Laravel, ไม่มี REST API แยก) | page navigation, form submission |
| Pinia (state management) | invoice draft, line items store |
| Tailwind CSS v3 | UI styling ทั้งหมด |
| Vue Draggable (SortableJS wrapper) | drag-to-reorder line items |
| Vue Final Modal | client picker modal, product picker modal |
| VueUse composables | `useLocalStorage`, `useDebounce` |
| Computed properties (reactive tax calculation) | `TaxSummary.vue` real-time update |
| Chart.js (หรือ vue-chartjs) | dashboard revenue chart |
| Vercel AI SDK (`streamText`) | streaming AI suggestions |

### Nice-to-have
- `@vueuse/motion` สำหรับ animation
- Vitest + Vue Test Utils สำหรับ unit test components

---

## 3. Database

### Must-have
| ทักษะ | ใช้ที่ไหน |
|---|---|
| MySQL 8 — UUID primary keys, ENUM, JSON column | schema ทั้งหมด |
| MySQL — indexes, foreign keys, ON DELETE CASCADE | performance + integrity |
| Redis — queue backend | Laravel Horizon |
| Redis — cache (PDF hash, invoice list, DBD lookup) | `isCacheValid()`, rate limit |

---

## 4. PDF Engine

### Must-have
| ทักษะ | ใช้ที่ไหน |
|---|---|
| Spatie Browsershot API | `InvoicePdfService::generate()` |
| Puppeteer / headless Chrome concepts | debug font issues, timeout tuning |
| HTML + CSS for print (A4, `@page`, `page-break`) | Blade PDF templates |
| CSS Grid + Flexbox | layout ใน PDF template |
| Base64 font embedding (`@font-face` + data URI) | inject Thai fonts ให้ Puppeteer |
| Thai fonts: Sarabun, NotoSansThai | แสดงข้อความภาษาไทยไม่แตก (tofu □) |
| Cloudflare R2 (S3-compatible) via `Storage::disk('r2')` | upload + presigned URL |
| SHA-256 cache hashing | `pdf_hash` เพื่อหลีกเลี่ยง regenerate ซ้ำ |

---

## 5. Thai Tax Domain Knowledge

### Must-have
| ความรู้ | รายละเอียด |
|---|---|
| VAT 7% (ภาษีมูลค่าเพิ่ม) | คิดบน subtotal หลัง discount |
| WHT — หัก ณ ที่จ่าย (1%, 3%, 5%) | คิดบน subtotal **ก่อน** VAT เสมอ (ตามกฎหมาย) |
| ภ.ง.ด. 3 (บุคคลธรรมดา) vs ภ.ง.ด. 53 (นิติบุคคล) | WHT rate ต่างกัน |
| เลขประจำตัวผู้เสียภาษี 13 หลัก | ใช้ใน DBD lookup + แสดงในใบแจ้งหนี้ |
| format เลข invoice ที่ถูกต้อง เช่น `INV-2026-0042` | `invoice_next_number` per company |
| สูตร: Total = subtotal + VAT − WHT | ลูกค้าโอนยอดนี้ให้ freelancer |
| PDPA (พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล) | export + delete account feature |

---

## 6. External API Integrations

| API | ใช้ทำอะไร | ทักษะที่ต้องการ |
|---|---|---|
| **Claude API** (claude-sonnet-4-6) | AI autofill line items, price suggestion | Anthropic SDK, prompt engineering, streaming |
| **Omise** | subscription billing, PromptPay, card | Omise PHP SDK, webhook verification |
| **DBD API** (กรมพัฒนาธุรกิจการค้า) | ค้นหาบริษัทจากเลขนิติบุคคล | HTTP client (Laravel Http facade), timeout/fallback |
| **Resend / SendGrid** | ส่ง invoice email + tracking pixel | transactional email API, 1×1 GIF tracking |
| **Pusher / Laravel Reverb** | broadcast `InvoicePdfReady` event | Laravel Broadcasting, Vue Echo |
| **Cloudflare R2** | PDF + logo storage | AWS SDK (S3-compatible), presigned URL |

---

## 7. DevOps & Infrastructure

### Must-have
| ทักษะ | ใช้ที่ไหน |
|---|---|
| Docker + Docker Compose | local dev: PHP, MySQL, Redis, Node.js (Puppeteer) |
| Azure DevOps Pipelines (YAML) | lint → PHPStan → test → deploy staging |
| Laravel Forge | server provisioning, deploy hook |
| Cloudflare (CDN + WAF) | ป้องกัน DDoS, serve assets |
| Sentry | error tracking (PHP + Vue) |
| Environment management (`.env`) | secrets สำหรับ API keys ทั้งหมด |

### Nice-to-have
- GitHub Actions (ถ้าย้ายจาก Azure DevOps)
- Terraform สำหรับ infra-as-code
- k6 / Artillery สำหรับ load testing PDF generation

---

## 8. Security

| ทักษะ | ใช้ที่ไหน |
|---|---|
| CSRF protection (Laravel default) | form submissions |
| Rate limiting (Laravel `throttle` middleware) | share link 100 req/min, PDF 5 req/min |
| Authorization (Laravel Policies) | ป้องกัน user A เข้า invoice ของ user B |
| Webhook signature verification | Omise webhook HMAC |
| Share token — random 64-char (`Str::random(64)`) | public invoice URL ไม่ enumerate ได้ |
| PDPA compliance | export JSON, hard delete ข้อมูลตาม request |

---

## 9. Testing

| ระดับ | Tool | Coverage เป้าหมาย |
|---|---|---|
| Unit test — TaxEngineService | PHPUnit | 100% — ทุก scenario VAT/WHT/discount |
| Unit test — InvoicePdfService cache logic | PHPUnit | hash comparison, cache hit/miss |
| Feature test — invoice CRUD | PHPUnit + Laravel testing helpers | happy path + validation errors |
| Frontend unit test | Vitest + Vue Test Utils | TaxSummary computed, LineItemsEditor |
| E2E (optional, Phase 2) | Playwright | create invoice → download PDF flow |

---

## 10. Skill Priority Matrix

```
Priority 1 (ต้องรู้ก่อน start):
  Laravel 11, Vue 3, Inertia.js, MySQL, TaxEngine (domain)

Priority 2 (ต้องรู้ใน Phase 2–3):
  Browsershot/Puppeteer, Blade PDF templates, R2 storage, Thai fonts

Priority 3 (ต้องรู้ใน Phase 4–5):
  Claude API streaming, Omise billing, Pusher broadcast, Email tracking

Priority 4 (polish / post-launch):
  PHPStan, load testing, Playwright E2E, Meilisearch
```

---

## 11. Learning Resources

| ทักษะ | แหล่ง |
|---|---|
| Inertia.js | https://inertiajs.com/getting-started |
| Spatie Browsershot | https://github.com/spatie/browsershot |
| Vercel AI SDK | https://sdk.vercel.ai/docs |
| Omise PHP | https://www.omise.co/libraries#php |
| Thai tax (WHT) | กรมสรรพากร rd.go.th — คู่มือ ภ.ง.ด. 3/53 |
| DBD Open API | https://openapi.dbd.go.th |
| Cloudflare R2 S3 compat | https://developers.cloudflare.com/r2/api/s3 |
