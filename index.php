<?php
session_start();
// If logged in, send to the dashboard (bank UI); otherwise show the public home page
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyWallet Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="topbar">
        <h2>MyWallet</h2>
        <?php if ($loggedIn): ?>
            <div style="display:flex;align-items:center;gap:12px;color:#fff">
                <span style="opacity:.9">Signed in as <?php echo htmlspecialchars($email); ?></span>
                <?php if ($role === 'admin'): ?> <a style="color:#fff;margin-left:8px" href="admin.php">Admin</a> <?php endif; ?>
                <a class="logout" href="logout.php">Log out</a>
            </div>
        <?php else: ?>
            <a class="logout" href="login.php">Log in</a>
        <?php endif; ?>
    </header>

    <main class="container">
        <section class="balance-card">
            <p>Available Balance</p>
            <h1>$20,000,450.75</h1>
            <button class="btn-primary">Add Money</button>
        </section>

        <section class="actions">
            <button>Send</button>
            <button>Request</button>
            <button>Withdraw</button>
        </section>

        <section class="transactions">
            <h3>Recent Activity</h3>
            <ul>
                <li>
                    <span>Payment Received</span>
                    <strong>+$100,220.00</strong>
                </li>
                <li>
                    <span>Online Purchase</span>
                    <strong>- $430.90</strong>
                </li>
                <li>
                    <span>Transfer Sent</span>
                    <strong>- $200.00</strong>
                </li>
                <li>
                    <span>Payment Received</span>
                    <strong>+9,000</strong>
                </li>
            </ul>
        </section>
    </main>
<script>
    let balance = 20000450.75;

    const balanceDisplay = document.querySelector(".balance-card h1");
    const transactionList = document.querySelector(".transactions ul");

    function formatMoney(amount) {
        return "$" + amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

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
