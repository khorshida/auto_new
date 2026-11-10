document.addEventListener('alpine:init', () => {
    window.Alpine.data('loanPlanner', (price, slug, whatsapp) => ({
        carSlug: slug,
        whatsapp,
        price: Number(price) || 0,
        down: Math.round((Number(price) || 0) * 0.2),
        years: 5,
        interest: 7.5,

        init() {
            this.$watch('price', (value) => {
                const next = Math.max(0, Number(value) || 0);

                if (next !== Number(this.price)) {
                    this.price = next;
                }

                if (Number(this.down) > next) {
                    this.down = next;
                }
            });
            this.$watch('down', (value) => {
                const next = Math.min(Math.max(0, Number(value) || 0), Math.max(0, Number(this.price) || 0));

                if (next !== Number(this.down)) {
                    this.down = next;
                }
            });
        },

        chooseCar(event) {
            const raw = event.target.selectedOptions[0]?.dataset.price ?? '';
            this.price = raw === '' ? 0 : Number(raw);
            this.down = Math.round(this.price * 0.2);
        },

        principal() {
            return Math.max(0, Number(this.price) - Number(this.down));
        },

        months() {
            return Number(this.years) * 12;
        },

        monthly(years = this.years) {
            const principal = this.principal();
            const months = Number(years) * 12;

            if (principal <= 0 || months <= 0) {
                return 0;
            }

            const rate = Number(this.interest) / 100 / 12;

            if (rate === 0) {
                return principal / months;
            }

            const factor = (1 + rate) ** months;

            return (principal * rate * factor) / (factor - 1);
        },

        totalPayable() {
            return this.monthly() * this.months();
        },

        totalInterest() {
            return Math.max(0, this.totalPayable() - this.principal());
        },

        downShare() {
            const price = Number(this.price);

            if (price <= 0) {
                return '0%';
            }

            return Math.round((Number(this.down) / price) * 100) + '%';
        },

        money(amount) {
            return '৳' + Math.round(amount).toLocaleString('en-US');
        },

        words(amount) {
            const value = Math.round(Number(amount) || 0);

            if (value >= 10000000) {
                return (value / 10000000).toFixed(2) + ' crore taka';
            }

            if (value >= 100000) {
                return (value / 100000).toFixed(2) + ' lakh taka';
            }

            return this.money(value);
        },

        tenures() {
            return [1, 2, 3, 4, 5, 6, 7].map((year) => ({
                year,
                monthly: this.monthly(year),
                interest: Math.max(0, this.monthly(year) * year * 12 - this.principal()),
            }));
        },

        schedule() {
            const rows = [];
            let balance = this.principal();
            const payment = this.monthly();
            const rate = Number(this.interest) / 100 / 12;

            for (let year = 1; year <= Number(this.years); year++) {
                let principalPaid = 0;
                let interestPaid = 0;

                for (let month = 0; month < 12; month++) {
                    const interestPart = rate === 0 ? 0 : balance * rate;
                    const principalPart = payment - interestPart;
                    interestPaid += interestPart;
                    principalPaid += principalPart;
                    balance -= principalPart;
                }

                rows.push({
                    year,
                    paid: payment * 12,
                    principalPaid,
                    interestPaid,
                    balance: Math.max(0, balance),
                });
            }

            return rows;
        },

        donut() {
            const down = Math.max(0, Number(this.down) || 0);
            const principal = this.principal();
            const interest = this.totalInterest();
            const total = down + principal + interest;

            if (total <= 0) {
                return { down: 0, principal: 0, interest: 0, principalOffset: 0, interestOffset: 0 };
            }

            const downShare = (down / total) * 100;
            const principalShare = (principal / total) * 100;
            const interestShare = (interest / total) * 100;

            return {
                down: downShare,
                principal: principalShare,
                interest: interestShare,
                principalOffset: downShare,
                interestOffset: downShare + principalShare,
            };
        },

        whatsappHref() {
            const text = `Hello, I would like to talk about a car loan. Price ${this.money(this.price)}, down payment ${this.money(this.down)}, ${this.years} years at ${this.interest}%. Monthly payment ${this.money(this.monthly())}.`;

            return `https://wa.me/${this.whatsapp}?text=${encodeURIComponent(text)}`;
        },
    }));
});
