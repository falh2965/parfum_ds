
// ══════════════════════════════
//  1. CONFIG
// ══════════════════════════════
const WHATSAPP_NUMBER = "212627665901"; // بدّل برقمك

// ══════════════════════════════
//  2. STATE
// ══════════════════════════════
let PRODUCTS = []; // غتتملا من قاعدة البيانات
let cart = {};
let activeCategory = "all";

// ══════════════════════════════
//  3. جيب المنتجات من PHP
// ══════════════════════════════
async function loadProducts() {
  try {
    const res  = await fetch('get_products.php');
    PRODUCTS   = await res.json();
    renderProducts();
  } catch (e) {
    console.error('خطأ في تحميل المنتجات', e);
  }
}

// ══════════════════════════════
//  4. RENDER PRODUCTS
// ══════════════════════════════
function renderProducts() {
  const q = document.getElementById("searchInput").value.trim().toLowerCase();

  const filtered = PRODUCTS.filter(p => {
    const matchCat = activeCategory === "all" || p.category === activeCategory;
    const matchQ   = !q || p.name.toLowerCase().includes(q) || p.description.includes(q);
    return matchCat && matchQ;
  });

  const grid = document.getElementById("grid");
  grid.innerHTML = "";

  if (!filtered.length) {
    grid.innerHTML = "<p style='text-align:center;padding:40px;color:gray'>لا توجد نتائج</p>";
    return;
  }

  filtered.forEach(p => {
    // صورة أو placeholder
    const imgHTML = p.image
      ? `<img src="imgs/${p.image}" class="card-img" alt="${p.name}"/>`
      : `<div class="card-emoji">🏺</div>`;

    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
      ${imgHTML}
      <div class="card-body">
        <p class="card-category">${p.category} · ${p.unit}</p>
        <h3 class="card-name">${p.name}</h3>
        <p class="card-desc">${p.description}</p>
        <div class="card-footer">
          <span class="card-price">${p.price} درهم</span>
          <button class="btn-order" onclick="addToCart(${p.id})">
            <i class="fa-solid fa-cart-plus"></i> اطلب الآن
          </button>
        </div>
      </div>
    `;
    grid.appendChild(card);
  });
}
// ══════════════════════════════
//  5. CART LOGIC
// ══════════════════════════════
function addToCart(id) {
  const product = PRODUCTS.find(p => p.id === id);
  if (!product) return;

  if (cart[id]) {
    cart[id].qty++; // موجود → زيد واحد
  } else {
    cart[id] = { ...product, qty: 1 }; // جديد
  }

  updateCartBadge();
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty += delta;
  if (cart[id].qty <= 0) delete cart[id]; // شيله إلا وصل 0
  updateCartBadge();
  renderDrawer(); // حدّث الدرار مباشرة
}

function updateCartBadge() {
  const total = Object.values(cart).reduce((sum, item) => sum + item.qty, 0);
  document.getElementById("cartCount").textContent = total;
}

// ══════════════════════════════
//  6. DRAWER (السلة)
// ══════════════════════════════
function renderDrawer() {
  const items = Object.values(cart);
  const cartItemsEl = document.getElementById("cartItems");
  const totalPriceEl = document.getElementById("totalPrice");
  const btnCheckout = document.getElementById("btnCheckout");

  if (!items.length) {
    cartItemsEl.innerHTML = "<p style='color:gray;text-align:center;padding:20px'>السلة فارغة</p>";
    totalPriceEl.textContent = "";
    btnCheckout.style.display = "none";
    return;
  }

  // ابني HTML ديال كل عنصر
  cartItemsEl.innerHTML = items.map(item => `
    <div class="cart-item">
      <div>
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-price">${item.price} درهم</div>
      </div>
      <div class="qty-wrap">
        <button class="qty-btn" onclick="changeQty(${item.id}, -1)">−</button>
        <span class="qty-num">${item.qty}</span>
        <button class="qty-btn" onclick="changeQty(${item.id}, +1)">+</button>
      </div>
    </div>
  `).join("");

  // حساب المجموع
  const total = items.reduce((sum, item) => sum + item.price * item.qty, 0);
  totalPriceEl.textContent = `المجموع: ${total} درهم`;
  btnCheckout.style.display = "block";
}

function openDrawer() {
  renderDrawer();
  document.getElementById("drawer").classList.add("open");
  document.getElementById("overlay").classList.add("open");
}

function closeDrawer() {
  document.getElementById("drawer").classList.remove("open");
  document.getElementById("overlay").classList.remove("open");
}

// ══════════════════════════════
//  7. WHATSAPP CHECKOUT
// ══════════════════════════════
function sendToWhatsApp() {
  const items = Object.values(cart);
  if (!items.length) return;

  // ابني نص الرسالة
  const lines = items.map(i =>
    `• ${i.name} x${i.qty} = ${i.price * i.qty} درهم`
  ).join("\n");

  const total = items.reduce((s, i) => s + i.price * i.qty, 0);

  const message =
`🌹 طلب جديد من عطور الفردوس

${lines}

──────────────
💰 المجموع: ${total} درهم

📦 شكراً، يرجى تأكيد العنوان للتوصيل`;

  // افتح واتساب بالرسالة
  const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
  window.open(url, "_blank");
}

// ══════════════════════════════
//  8. EVENTS
// ══════════════════════════════

// فلتر الكاتيغوري
document.querySelectorAll(".filter-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    activeCategory = btn.dataset.cat;
    renderProducts();
  });
});

// البحث
document.getElementById("searchInput").addEventListener("input", renderProducts);

// فتح/إغلاق السلة
document.getElementById("cartFab").addEventListener("click", openDrawer);
document.getElementById("overlay").addEventListener("click", closeDrawer);

// إرسال الطلب
document.getElementById("btnCheckout").addEventListener("click", sendToWhatsApp);

// ══════════════════════════════
//  9. INIT
// ══════════════════════════════
loadProducts();
