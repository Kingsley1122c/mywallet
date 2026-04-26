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
    <title>Investment Hub | MyWallet</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .investment-container {
            max-width: 1400px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.5);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        }
        
        .stat-label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .stat-change {
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .stat-change.positive {
            color: #10b981;
        }
        
        .stat-change.negative {
            color: #ef4444;
        }
        
        .investment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .investment-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .investment-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(102,126,234,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .investment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .investment-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .investment-card.stocks .investment-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .investment-card.crypto .investment-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .investment-card.bonds .investment-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .investment-card.real-estate .investment-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        .investment-card.savings .investment-icon {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        }
        
        .investment-card.commodities .investment-icon {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }
        
        .investment-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .investment-desc {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .investment-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .investment-stat {
            text-align: center;
        }
        
        .investment-stat-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .investment-stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .investment-actions {
            display: flex;
            gap: 12px;
            position: relative;
            z-index: 1;
        }
        
        .btn-invest {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-invest.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-invest.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.4);
        }
        
        .btn-invest.secondary {
            background: #f3f4f6;
            color: #667eea;
        }
        
        .btn-invest.secondary:hover {
            background: #e5e7eb;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .modal-close {
            background: #f3f4f6;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            background: #e5e7eb;
            color: #1f2937;
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
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            background: white;
            transition: all 0.2s ease;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .investment-container {
                padding: 16px;
            }
            
            .page-header {
                padding: 24px;
                flex-direction: column;
                align-items: stretch;
            }
            
            .page-header h1 {
                font-size: 24px;
            }
            
            .header-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .investment-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .investment-card {
                padding: 24px;
            }
            
            .modal-content {
                padding: 28px;
            }
            
            .stat-value {
                font-size: 28px;
            }
        }
        
        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 20px;
            }
            
            .stat-value {
                font-size: 24px;
            }
            
            .investment-title {
                font-size: 20px;
            }
            
            .investment-actions {
                flex-direction: column;
            }
            
            .btn-invest {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="investment-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>💼 Investment Hub</h1>
                <p style="color: #6b7280; margin: 8px 0 0 0;">Grow your wealth with smart investments</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <button onclick="showModal('addInvestment')" class="btn btn-primary">+ New Investment</button>
            </div>
        </div>
        
        <!-- Portfolio Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Portfolio</div>
                <div class="stat-value">$24,580</div>
                <span class="stat-change positive">↑ 12.5% this month</span>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Invested</div>
                <div class="stat-value">$18,000</div>
                <span class="stat-change positive">↑ $2,000 this month</span>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Returns</div>
                <div class="stat-value">$6,580</div>
                <span class="stat-change positive">↑ 36.6% ROI</span>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Investments</div>
                <div class="stat-value">12</div>
                <span class="stat-change positive">↑ 3 new this month</span>
            </div>
        </div>
        
        <!-- Investment Options -->
        <div class="investment-grid">
            <!-- Stocks -->
            <div class="investment-card stocks">
                <div class="investment-icon">📈</div>
                <h3 class="investment-title">Stock Market</h3>
                <p class="investment-desc">Invest in top-performing companies and ETFs. Build a diversified portfolio with blue-chip stocks.</p>
                <div class="investment-stats">
                    <div class="investment-stat">
                        <div class="investment-stat-label">Avg Return</div>
                        <div class="investment-stat-value">14.2%</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Min Amount</div>
                        <div class="investment-stat-value">$100</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Risk Level</div>
                        <div class="investment-stat-value">Medium</div>
                    </div>
                </div>
                <div class="investment-actions">
                    <button class="btn-invest primary" onclick="showModal('stocks')">Invest Now</button>
                    <button class="btn-invest secondary" onclick="alert('View stock portfolio')">View Details</button>
                </div>
            </div>
            
            <!-- Cryptocurrency -->
            <div class="investment-card crypto">
                <div class="investment-icon">₿</div>
                <h3 class="investment-title">Cryptocurrency</h3>
                <p class="investment-desc">Trade Bitcoin, Ethereum, and other digital assets. High-growth potential with 24/7 trading.</p>
                <div class="investment-stats">
                    <div class="investment-stat">
                        <div class="investment-stat-label">Avg Return</div>
                        <div class="investment-stat-value">28.5%</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Min Amount</div>
                        <div class="investment-stat-value">$50</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Risk Level</div>
                        <div class="investment-stat-value">High</div>
                    </div>
                </div>
                <div class="investment-actions">
                    <button class="btn-invest primary" onclick="showModal('crypto')">Invest Now</button>
                    <button class="btn-invest secondary" onclick="alert('View crypto portfolio')">View Details</button>
                </div>
            </div>
            
            <!-- Bonds -->
            <div class="investment-card bonds">
                <div class="investment-icon">📜</div>
                <h3 class="investment-title">Bonds & Fixed Income</h3>
                <p class="investment-desc">Safe and steady returns with government and corporate bonds. Perfect for risk-averse investors.</p>
                <div class="investment-stats">
                    <div class="investment-stat">
                        <div class="investment-stat-label">Avg Return</div>
                        <div class="investment-stat-value">5.8%</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Min Amount</div>
                        <div class="investment-stat-value">$500</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Risk Level</div>
                        <div class="investment-stat-value">Low</div>
                    </div>
                </div>
                <div class="investment-actions">
                    <button class="btn-invest primary" onclick="showModal('bonds')">Invest Now</button>
                    <button class="btn-invest secondary" onclick="alert('View bonds portfolio')">View Details</button>
                </div>
            </div>
            
            <!-- Real Estate -->
            <div class="investment-card real-estate">
                <div class="investment-icon">🏠</div>
                <h3 class="investment-title">Real Estate</h3>
                <p class="investment-desc">Invest in property and REITs. Generate passive income through rental yields and appreciation.</p>
                <div class="investment-stats">
                    <div class="investment-stat">
                        <div class="investment-stat-label">Avg Return</div>
                        <div class="investment-stat-value">11.3%</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Min Amount</div>
                        <div class="investment-stat-value">$1,000</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Risk Level</div>
                        <div class="investment-stat-value">Medium</div>
                    </div>
                </div>
                <div class="investment-actions">
                    <button class="btn-invest primary" onclick="showModal('realestate')">Invest Now</button>
                    <button class="btn-invest secondary" onclick="alert('View properties')">View Details</button>
                </div>
            </div>
            
            <!-- High-Yield Savings -->
            <div class="investment-card savings">
                <div class="investment-icon">💰</div>
                <h3 class="investment-title">High-Yield Savings</h3>
                <p class="investment-desc">Earn competitive interest rates with zero risk. FDIC insured up to $250,000 per account.</p>
                <div class="investment-stats">
                    <div class="investment-stat">
                        <div class="investment-stat-label">Avg Return</div>
                        <div class="investment-stat-value">4.5%</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Min Amount</div>
                        <div class="investment-stat-value">$10</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Risk Level</div>
                        <div class="investment-stat-value">None</div>
                    </div>
                </div>
                <div class="investment-actions">
                    <button class="btn-invest primary" onclick="showModal('savings')">Open Account</button>
                    <button class="btn-invest secondary" onclick="alert('View savings')">View Details</button>
                </div>
            </div>
            
            <!-- Commodities -->
            <div class="investment-card commodities">
                <div class="investment-icon">⚡</div>
                <h3 class="investment-title">Commodities</h3>
                <p class="investment-desc">Trade gold, silver, oil, and other physical assets. Hedge against inflation and market volatility.</p>
                <div class="investment-stats">
                    <div class="investment-stat">
                        <div class="investment-stat-label">Avg Return</div>
                        <div class="investment-stat-value">9.7%</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Min Amount</div>
                        <div class="investment-stat-value">$250</div>
                    </div>
                    <div class="investment-stat">
                        <div class="investment-stat-label">Risk Level</div>
                        <div class="investment-stat-value">Medium</div>
                    </div>
                </div>
                <div class="investment-actions">
                    <button class="btn-invest primary" onclick="showModal('commodities')">Invest Now</button>
                    <button class="btn-invest secondary" onclick="alert('View commodities')">View Details</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Investment Modal -->
    <div class="modal" id="investmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Make Investment</h3>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <form onsubmit="submitInvestment(event)">
                <div class="form-group">
                    <label class="form-label">Investment Type</label>
                    <select class="form-select" id="investmentType" required>
                        <option value="">Select type...</option>
                        <option value="stocks">Stocks</option>
                        <option value="crypto">Cryptocurrency</option>
                        <option value="bonds">Bonds</option>
                        <option value="realestate">Real Estate</option>
                        <option value="savings">High-Yield Savings</option>
                        <option value="commodities">Commodities</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (USD)</label>
                    <input type="number" class="form-input" id="investmentAmount" placeholder="$0.00" min="10" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Investment Period</label>
                    <select class="form-select" id="investmentPeriod" required>
                        <option value="">Select period...</option>
                        <option value="short">Short-term (1-6 months)</option>
                        <option value="medium">Medium-term (6-24 months)</option>
                        <option value="long">Long-term (2+ years)</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Confirm Investment</button>
            </form>
        </div>
    </div>
    
    <script>
        function showModal(type) {
            const modal = document.getElementById('investmentModal');
            const typeSelect = document.getElementById('investmentType');
            
            if (type !== 'addInvestment') {
                typeSelect.value = type;
            }
            
            modal.classList.add('active');
        }
        
        function closeModal() {
            const modal = document.getElementById('investmentModal');
            modal.classList.remove('active');
        }
        
        function submitInvestment(e) {
            e.preventDefault();
            
            const type = document.getElementById('investmentType').value;
            const amount = document.getElementById('investmentAmount').value;
            const period = document.getElementById('investmentPeriod').value;
            
            alert(`Investment submitted!\nType: ${type}\nAmount: $${amount}\nPeriod: ${period}\n\nThis is a demo. In production, this would process the investment.`);
            
            closeModal();
            
            // Reset form
            e.target.reset();
        }
        
        // Close modal on background click
        document.getElementById('investmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
