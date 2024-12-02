import { Controller } from "stimulus";

export default class extends Controller {
  static targets = ["cartSidebar", "cartIcon"];

  async addToCart(event) {
    event.preventDefault(); 
    const form = event.target.closest('form'); 
    const response = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form)
    });

    if (response.ok) {
      const cartHtml = await response.text();
      this.cartSidebarTarget.innerHTML = cartHtml; 
      this.updateCartIcon(); 
    } else {
      alert('Erreur lors de l\'ajout au panier.');
    }
  }

  async removeFromCart(event) {
    const productId = event.target.getAttribute('data-product-id');
    
    const response = await fetch(`/shop/remove/${productId}`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest', 
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ productId })
    });

    if (response.ok) {
      const cartHtml = await response.text();
      this.cartSidebarTarget.innerHTML = cartHtml; 
      this.updateCartIcon(); 
    } else {
      alert('Erreur lors de la suppression du produit.');
    }
  }

  updateCartIcon() {
    const itemCount = this.cartSidebarTarget.querySelectorAll('tbody tr').length;
    this.cartIconTarget.innerHTML = `${itemCount} Articles`;
  }
}
