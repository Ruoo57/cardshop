import { Controller } from "stimulus";

export default class extends Controller {
    static targets = ["formContainer", "numberOfCards"];

    updateForms() {
        const numberOfCards = parseInt(this.numberOfCardsTarget.value);
        this.formContainerTarget.innerHTML = '';

        for (let i = 0; i < numberOfCards; i++) {
            const form = document.createElement('div');
            form.innerHTML = `
                <div class="card-form">
                    <h3>Credit Card ${i + 1}</h3>
                    <label for="credit_cards_${i}_number">Card Number:</label>
                    <input type="text" id="credit_cards_${i}_number" name="credit_cards[${i}][number]" placeholder="Card Number" required>
                    <label for="credit_cards_${i}_expirationDate">Expiration Date (MM/YY):</label>
                    <input type="text" id="credit_cards_${i}_expirationDate" name="credit_cards[${i}][expirationDate]" placeholder="MM/YY" required>
                    <label for="credit_cards_${i}_cvv">CVV:</label>
                    <input type="text" id="credit_cards_${i}_cvv" name="credit_cards[${i}][cvv]" placeholder="CVV" required>
                </div>
            `;
            this.formContainerTarget.appendChild(form);
        }
    }
}
