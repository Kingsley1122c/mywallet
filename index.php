<?php
session_start();
// Logged-in users still land on the dashboard, but guests should get the public
// homepage directly at / so search engines do not see the root URL as a redirect.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

header('Content-Type: text/html; charset=UTF-8');
readfile(__DIR__ . '/index.html');
exit();
?>

    function updateBalance() {
        balanceDisplay.textContent = formatMoney(balance);
    }

    function addTransaction(text, amount) {
        const li = document.createElement("li");

        const span = document.createElement("span");
        span.textContent = text;

        const strong = document.createElement("strong");
        strong.textContent = (amount > 0 ? "+" : "-") + formatMoney(Math.abs(amount));

        li.appendChild(span);
        li.appendChild(strong);
        transactionList.prepend(li);
    }

    // Add Money
    document.querySelector(".btn-primary").addEventListener("click", () => {
        let amount = prompt("Enter amount to add:");

        amount = parseFloat(amount);
        if (!amount || amount <= 0) return alert("Invalid amount");

        balance += amount;
        updateBalance();
        addTransaction("Money Added", amount);
    });

    // Send
    document.querySelectorAll(".actions button")[0].addEventListener("click", () => {
        let amount = prompt("Enter amount to send:");

        amount = parseFloat(amount);
        if (!amount || amount <= 0) return alert("Invalid amount");
        if (amount > balance) return alert("Insufficient balance");

        balance -= amount;
        updateBalance();
        addTransaction("Transfer Sent", -amount);
    });

    // Request
    document.querySelectorAll(".actions button")[1].addEventListener("click", () => {
        let amount = prompt("Enter requested amount:");

        amount = parseFloat(amount);
        if (!amount || amount <= 0) return alert("Invalid amount");

        balance += amount;
        updateBalance();
        addTransaction("Payment Received", amount);
    });

    // Withdraw
    document.querySelectorAll(".actions button")[2].addEventListener("click", () => {
        let amount = prompt("Enter amount to withdraw:");

        amount = parseFloat(amount);
        if (!amount || amount <= 0) return alert("Invalid amount");
        if (amount > balance) return alert("Insufficient balance");

        balance -= amount;
        updateBalance();
        addTransaction("Withdrawal", -amount);
    });

    // Initial load
    updateBalance();
</script>
</body>
</html>
