<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loans & Credit | Mivonta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .loans-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .page-header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .page-header h1 {
            margin: 0 0 8px 0;
            font-size: 32px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-nav {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .nav-tab {
            padding: 12px 24px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #6b7280;
        }
        
        .nav-tab.active {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-color: transparent;
        }
        
        .nav-tab:hover:not(.active) {
            border-color: #f093fb;
            color: #f093fb;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        .loan-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .loan-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .loan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
        }
        
        .loan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        }
        
        .loan-type {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .loan-name {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
        }
        
        .loan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        
        .loan-features li {
            padding: 8px 0;
            color: #4b5563;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .loan-features li::before {
            content: '✓';
            color: #10b981;
            font-weight: bold;
            font-size: 16px;
        }
        
        .loan-highlight {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 16px;
            background: #f9fafb;
            border-radius: 12px;
        }
        
        .highlight-item {
            text-align: center;
        }
        
        .highlight-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .highlight-value {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .btn-apply {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(240,147,251,0.4);
        }
        
        .calculator-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .calculator-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.2s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #f093fb;
            box-shadow: 0 0 0 4px rgba(240,147,251,0.1);
        }
        
        .calculation-result {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 24px;
            border-radius: 16px;
            margin-top: 20px;
            color: white;
        }
        
        .result-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .result-value {
            font-size: 36px;
            font-weight: 700;
        }
        
        .btn-back {
            padding: 12px 24px;
            background: white;
            color: #f093fb;
            border: 2px solid #f093fb;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }
        
        .btn-back:hover {
            background: #f093fb;
            color: white;
        }
        
        /* Coming Soon Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .coming-soon-modal {
            background: white;
            border-radius: 32px;
            padding: 48px 40px;
            max-width: 480px;
            width: 90%;
            text-align: center;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
            transform: scale(0.7) translateY(50px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }
        
        .modal-overlay.active .coming-soon-modal {
            transform: scale(1) translateY(0);
        }
        
        .coming-soon-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #f093fb 0%, #f5576c 50%, #f093fb 100%);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .modal-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(240, 147, 251, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(240, 147, 251, 0);
            }
        }
        
        .modal-icon::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border: 3px dashed rgba(240, 147, 251, 0.3);
            border-radius: 50%;
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .modal-title {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }
        
        .modal-subtitle {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .modal-features {
            list-style: none;
            padding: 0;
            margin: 24px 0;
            text-align: left;
        }
        
        .modal-features li {
            padding: 12px 0;
            color: #4b5563;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .modal-features li::before {
            content: '🚀';
            font-size: 20px;
        }
        
        .btn-close-modal {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-close-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(240, 147, 251, 0.5);
        }
        
        .btn-close-modal:active {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .loans-container {
                padding: 16px;
            }
            
            .page-header {
                padding: 24px;
            }
            
            .page-header h1 {
                font-size: 24px;
            }
            
            .header-nav {
                flex-direction: column;
            }
            
            .nav-tab {
                width: 100%;
                text-align: center;
            }
            
            .loan-cards {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .loan-card {
                padding: 24px;
            }
            
            .loan-name {
                font-size: 20px;
            }
            
            .loan-highlight {
                flex-direction: column;
                gap: 12px;
            }
            
            .coming-soon-modal {
                padding: 36px 28px;
                border-radius: 24px;
            }
            
            .modal-icon {
                width: 100px;
                height: 100px;
                font-size: 52px;
            }
            
            .modal-title {
                font-size: 26px;
            }
            
            .modal-subtitle {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="loans-container">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        
        <div class="page-header">
            <h1>💳 Loans & Credit</h1>
            <p style="color: #6b7280; margin: 0;">Access credit when you need it most</p>
            
            <div class="header-nav">
                <button class="nav-tab active" onclick="showSection('loans')">Personal Loans</button>
                <button class="nav-tab" onclick="showSection('credit')">Credit Cards</button>
                <button class="nav-tab" onclick="showSection('calculator')">Calculator</button>
            </div>
        </div>
        
        <!-- Personal Loans Section -->
        <div id="loans-section" class="content-section active">
            <div class="loan-cards">
                <div class="loan-card">
                    <div class="loan-type">Personal Loan</div>
                    <h3 class="loan-name">Quick Cash Loan</h3>
                    <ul class="loan-features">
                        <li>Instant approval in minutes</li>
                        <li>No collateral required</li>
                        <li>Flexible repayment terms</li>
                        <li>Competitive interest rates</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">APR From</div>
                            <div class="highlight-value">5.99%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Max Amount</div>
                            <div class="highlight-value">$50K</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Term</div>
                            <div class="highlight-value">1-5 yrs</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('Quick Cash Loan')">Apply Now</button>
                </div>
                
                <div class="loan-card">
                    <div class="loan-type">Home Loan</div>
                    <h3 class="loan-name">Mortgage Financing</h3>
                    <ul class="loan-features">
                        <li>Up to 90% LTV financing</li>
                        <li>Fixed & variable rates</li>
                        <li>Pre-approval available</li>
                        <li>First-time buyer programs</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">APR From</div>
                            <div class="highlight-value">3.25%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Max Amount</div>
                            <div class="highlight-value">$1M</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Term</div>
                            <div class="highlight-value">15-30 yrs</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('Mortgage Financing')">Apply Now</button>
                </div>
                
                <div class="loan-card">
                    <div class="loan-type">Auto Loan</div>
                    <h3 class="loan-name">Car Financing</h3>
                    <ul class="loan-features">
                        <li>New & used car financing</li>
                        <li>Refinancing available</li>
                        <li>100% online application</li>
                        <li>Fast fund disbursement</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">APR From</div>
                            <div class="highlight-value">4.49%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Max Amount</div>
                            <div class="highlight-value">$100K</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Term</div>
                            <div class="highlight-value">1-7 yrs</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('Car Financing')">Apply Now</button>
                </div>
                
                <div class="loan-card">
                    <div class="loan-type">Business Loan</div>
                    <h3 class="loan-name">SME Financing</h3>
                    <ul class="loan-features">
                        <li>Working capital loans</li>
                        <li>Equipment financing</li>
                        <li>Business expansion funds</li>
                        <li>Line of credit available</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">APR From</div>
                            <div class="highlight-value">6.99%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Max Amount</div>
                            <div class="highlight-value">$500K</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Term</div>
                            <div class="highlight-value">1-10 yrs</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('SME Financing')">Apply Now</button>
                </div>
            </div>
        </div>
        
        <!-- Credit Cards Section -->
        <div id="credit-section" class="content-section">
            <div class="loan-cards">
                <div class="loan-card">
                    <div class="loan-type">Rewards Card</div>
                    <h3 class="loan-name">Platinum Rewards</h3>
                    <ul class="loan-features">
                        <li>3% cashback on all purchases</li>
                        <li>0% APR for 12 months</li>
                        <li>No annual fee first year</li>
                        <li>Travel insurance included</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">Credit Limit</div>
                            <div class="highlight-value">$25K</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">APR</div>
                            <div class="highlight-value">15.99%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Annual Fee</div>
                            <div class="highlight-value">$95</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('Platinum Rewards Card')">Apply Now</button>
                </div>
                
                <div class="loan-card">
                    <div class="loan-type">Travel Card</div>
                    <h3 class="loan-name">Elite Travel</h3>
                    <ul class="loan-features">
                        <li>5x points on travel & dining</li>
                        <li>Airport lounge access</li>
                        <li>Priority boarding benefits</li>
                        <li>Travel delay protection</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">Credit Limit</div>
                            <div class="highlight-value">$50K</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">APR</div>
                            <div class="highlight-value">16.99%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Annual Fee</div>
                            <div class="highlight-value">$195</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('Elite Travel Card')">Apply Now</button>
                </div>
                
                <div class="loan-card">
                    <div class="loan-type">Cashback Card</div>
                    <h3 class="loan-name">Ultimate Cashback</h3>
                    <ul class="loan-features">
                        <li>5% cashback on rotating categories</li>
                        <li>2% on grocery & gas</li>
                        <li>1% on everything else</li>
                        <li>No cashback limits</li>
                    </ul>
                    <div class="loan-highlight">
                        <div class="highlight-item">
                            <div class="highlight-label">Credit Limit</div>
                            <div class="highlight-value">$15K</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">APR</div>
                            <div class="highlight-value">14.99%</div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-label">Annual Fee</div>
                            <div class="highlight-value">$0</div>
                        </div>
                    </div>
                    <button class="btn-apply" onclick="applyLoan('Ultimate Cashback Card')">Apply Now</button>
                </div>
            </div>
        </div>
        
        <!-- Loan Calculator Section -->
        <div id="calculator-section" class="content-section">
            <div class="calculator-card">
                <h3 class="calculator-title">💰 Loan Payment Calculator</h3>
                <form onsubmit="calculateLoan(event)">
                    <div class="form-group">
                        <label class="form-label">Loan Amount ($)</label>
                        <input type="number" class="form-input" id="loanAmount" placeholder="10000" min="100" step="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interest Rate (% per year)</label>
                        <input type="number" class="form-input" id="interestRate" placeholder="5.5" min="0" max="100" step="0.1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Loan Term (months)</label>
                        <input type="number" class="form-input" id="loanTerm" placeholder="36" min="1" max="360" required>
                    </div>
                    <button type="submit" class="btn-apply">Calculate Payment</button>
                </form>
                
                <div id="calculationResult" style="display: none;">
                    <div class="calculation-result">
                        <div class="result-label">Monthly Payment</div>
                        <div class="result-value" id="monthlyPayment">$0.00</div>
                    </div>
                    <div style="margin-top: 20px; padding: 20px; background: #f9fafb; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="color: #6b7280;">Total Interest:</span>
                            <span style="font-weight: 700; color: #1f2937;" id="totalInterest">$0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280;">Total Payment:</span>
                            <span style="font-weight: 700; color: #1f2937;" id="totalPayment">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Coming Soon Modal -->
    <div class="modal-overlay" id="comingSoonModal" onclick="closeModal(event)">
        <div class="coming-soon-modal" onclick="event.stopPropagation()">
            <div class="modal-icon">🚀</div>
            <h2 class="modal-title">Coming Soon!</h2>
            <p class="modal-subtitle">We're working hard to bring you amazing loan services. Stay tuned for something special!</p>
            <ul class="modal-features">
                <li>Instant loan approvals</li>
                <li>Competitive interest rates</li>
                <li>Flexible repayment options</li>
                <li>100% digital process</li>
            </ul>
            <button class="btn-close-modal" onclick="closeModal()">Got It!</button>
        </div>
    </div>
    
    <script>
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            
            // Show selected section
            document.getElementById(section + '-section').classList.add('active');
            event.target.classList.add('active');
        }
        
        function applyLoan(loanType) {
            // Show the coming soon modal
            const modal = document.getElementById('comingSoonModal');
            modal.classList.add('active');
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(event) {
            // Close if clicking overlay or called directly
            if (!event || event.target.classList.contains('modal-overlay')) {
                const modal = document.getElementById('comingSoonModal');
                modal.classList.remove('active');
                
                // Restore body scroll
                document.body.style.overflow = '';
            }
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        function calculateLoan(e) {
            e.preventDefault();
            
            const principal = parseFloat(document.getElementById('loanAmount').value);
            const annualRate = parseFloat(document.getElementById('interestRate').value);
            const months = parseInt(document.getElementById('loanTerm').value);
            
            // Calculate monthly payment using amortization formula
            const monthlyRate = annualRate / 100 / 12;
            const monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
            
            const totalPayment = monthlyPayment * months;
            const totalInterest = totalPayment - principal;
            
            // Display results
            document.getElementById('monthlyPayment').textContent = '$' + monthlyPayment.toFixed(2);
            document.getElementById('totalInterest').textContent = '$' + totalInterest.toFixed(2);
            document.getElementById('totalPayment').textContent = '$' + totalPayment.toFixed(2);
            document.getElementById('calculationResult').style.display = 'block';
        }
    </script>
</body>
</html>
