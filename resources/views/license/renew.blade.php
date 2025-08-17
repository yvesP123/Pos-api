<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew License</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Add CSRF token -->
    <style>
          .disabled-option {
            opacity: 0.7;
            cursor: not-allowed;
            background-color: #f9fafb;
        }
        .disabled-option:hover {
            box-shadow: none;
            transform: none;
        }
        .unavailable-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ef4444;
            color: white;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 9999px;
            transform: rotate(5deg);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Renew Your License</h1>
                <p class="text-gray-600">Choose your preferred renewal method</p>
            </div>

            <!-- Current License Info -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-800">Current License Status</h3>
                </div>
                <div class="text-sm space-y-1">
                    <p><strong>Company ID:</strong> {{ $license->company_id }}</p>
                    <p><strong>Current Expiry:</strong> {{ $license->expiry_date->format('d/m/Y') }}</p>
                    <p class="text-red-600 font-semibold">Status: EXPIRED</p>
                </div>
            </div>

            <!-- Renewal Options -->
            <div class="space-y-4 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Select Renewal Method</h2>
                
                <!-- Option 1: Manual Key -->
                <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:shadow-md transition-all duration-200" 
                     id="manualOption" onclick="selectOption('manual')">
                    <div class="flex items-start">
                        <input type="radio" name="renewal_option" value="manual" id="manual_radio" class="mt-1 mr-4">
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3.586l6.879-6.88a6 6 0 018.242 0z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Use License Key Manually</h3>
                            </div>
                            <p class="text-gray-600 mb-3">I have a license key that I want to activate manually</p>
                            <div class="text-sm text-gray-500">
                                ✓ Instant activation<br>
                                ✓ No payment required<br>
                                ✓ Use existing license key
                            </div>
                        </div>
                    </div>
                </div>

               <!-- Option 2: Direct Payment (Disabled) -->
                <div class="border-2 border-gray-200 rounded-lg p-6 disabled-option relative" 
                     id="paymentOption">
                    <div class="unavailable-badge">Unavailable</div>
                    <div class="flex items-start">
                        <input type="radio" name="renewal_option" value="payment" id="payment_radio" class="mt-1 mr-4" disabled>
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <svg class="w-6 h-6 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-600">Pay Directly for Renewal</h3>
                            </div>
                            <p class="text-gray-500 mb-3">Purchase license extension with flexible payment options</p>
                            <div class="text-sm text-gray-400">
                                ✓ Multiple duration options<br>
                                ✓ Card & Mobile Money payment<br>
                                ✓ Automatic license extension
                            </div>
                            <div class="mt-4 p-3 bg-gray-100 rounded-lg border border-gray-200">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600">This option is temporarily unavailable. Please use the manual license key method to renew your license.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Key Form -->
            <div id="manualForm" class="hidden">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Enter Your License Key</h3>
                    
                    <form id="manualRenewalForm" class="space-y-4">
                        <div>
                            <label for="license_key" class="block text-sm font-medium text-gray-700 mb-2">
                                License Key *
                            </label>
                            <textarea name="license_key" id="license_key" rows="4" 
                                      placeholder="Paste your complete license key here..."
                                      class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm resize-none" required></textarea>
                            <p class="text-xs text-gray-500 mt-2">Enter the complete license key provided by your administrator</p>
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" id="validateBtn" 
                            class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                            VALIDATE KEY
                        </button>
                            <button type="submit" 
                                    class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition duration-200 font-semibold">
                                RENEW LICENSE
                            </button>
                        </div>
                        <!-- Add this below the validation result div -->
<div id="validationDetails" class="hidden mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
    <h4 class="font-semibold text-gray-800 mb-2">Key Details:</h4>
    <pre id="keyDetails" class="font-mono text-sm bg-white p-3 rounded"></pre>
</div>
                    </form>

                    <!-- Validation Result -->
                    <div id="validationResult" class="hidden mt-4 p-3 rounded-lg">
                        <div id="validationMessage"></div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div id="paymentForm" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Purchase License Extension</h3>
                    
                    <form id="paymentRenewalForm" class="space-y-6">
                        <!-- Duration Selection -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-3">
                                Select License Duration *
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="relative">
                                    <input type="radio" name="duration" value="30" data-price="10" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-colors duration-selection">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-semibold text-gray-800">1 Month</div>
                                                <div class="text-sm text-gray-600">30 Days</div>
                                            </div>
                                            <div class="text-lg font-bold text-green-600">$10</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="duration" value="90" data-price="25" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-colors duration-selection">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-semibold text-gray-800">3 Months</div>
                                                <div class="text-sm text-gray-600">90 Days</div>
                                            </div>
                                            <div class="text-lg font-bold text-green-600">$25</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="duration" value="180" data-price="45" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-colors duration-selection">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-semibold text-gray-800">6 Months</div>
                                                <div class="text-sm text-gray-600">180 Days</div>
                                            </div>
                                            <div class="text-lg font-bold text-green-600">$45</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="duration" value="365" data-price="80" class="sr-only">
                                    <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-colors duration-selection">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-semibold text-gray-800">1 Year</div>
                                                <div class="text-sm text-gray-600">365 Days</div>
                                            </div>
                                            <div class="text-lg font-bold text-green-600">$80</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Select Payment Method *
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition-colors">
                                    <input type="radio" name="payment_method" value="card" class="h-4 w-4 text-green-600 mr-4">
                                    <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <div>
                                        <div class="font-semibold text-gray-800">Credit/Debit Card</div>
                                        <div class="text-sm text-gray-600">Visa, Mastercard, American Express</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition-colors">
                                    <input type="radio" name="payment_method" value="momo" class="h-4 w-4 text-green-600 mr-4">
                                    <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <div class="font-semibold text-gray-800">Mobile Money</div>
                                        <div class="text-sm text-gray-600">MTN, Airtel, Tigo Cash</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Card Payment Details -->
                        <div id="cardDetails" class="hidden space-y-4 bg-white border border-gray-300 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Card Details</h4>
                            <div class="space-y-4">
                                <div>
                                    <label for="card_number" class="block text-sm font-medium text-gray-700 mb-1">
                                        Card Number *
                                    </label>
                                    <input type="text" id="card_number" name="card_number" 
                                           placeholder="1234 5678 9012 3456" maxlength="19"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                                            Expiry Date *
                                        </label>
                                        <input type="text" id="expiry_date" name="expiry_date" 
                                               placeholder="MM/YY" maxlength="5"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label for="cvv" class="block text-sm font-medium text-gray-700 mb-1">
                                            CVV *
                                        </label>
                                        <input type="text" id="cvv" name="cvv" 
                                               placeholder="123" maxlength="4"
                                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                </div>
                                <div>
                                    <label for="card_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Cardholder Name *
                                    </label>
                                    <input type="text" id="card_name" name="card_name" 
                                           placeholder="John Doe"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Money Details -->
                        <div id="momoDetails" class="hidden space-y-4 bg-white border border-gray-300 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Mobile Money Details</h4>
                            <div class="space-y-4">
                                <div>
                                    <label for="momo_provider" class="block text-sm font-medium text-gray-700 mb-2">
                                        Select Provider *
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="momo_provider" value="mtn" class="h-4 w-4 text-green-600 mr-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs font-bold text-black">MTN</span>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-800">MTN Mobile Money</div>
                                                    <div class="text-sm text-gray-600">Rwanda's leading mobile money service</div>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="momo_provider" value="airtel" class="h-4 w-4 text-green-600 mr-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs font-bold text-white">AIR</span>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-800">Airtel Money</div>
                                                    <div class="text-sm text-gray-600">Fast and secure mobile payments</div>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="momo_provider" value="tigo" class="h-4 w-4 text-green-600 mr-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                                    <span class="text-xs font-bold text-white">TGO</span>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-800">Tigo Cash</div>
                                                    <div class="text-sm text-gray-600">Easy mobile money transactions</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label for="momo_number" class="block text-sm font-medium text-gray-700 mb-1">
                                        Mobile Number *
                                    </label>
                                    <input type="tel" id="momo_number" name="momo_number" 
                                           placeholder="+250 7XX XXX XXX"
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <p class="text-xs text-gray-500 mt-1">Enter the mobile number registered with your mobile money account</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div id="orderSummary" class="hidden bg-white border border-gray-300 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Order Summary</h4>
                            <div class="flex justify-between text-sm mb-2">
                                <span id="summaryDuration">-</span>
                                <span id="summaryPrice">$0</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total</span>
                                <span id="totalPrice" class="text-green-600">$0</span>
                            </div>
                        </div>

                        <!-- Payment Result -->
                        <div id="paymentResult" class="hidden mt-4 p-3 rounded-lg">
                            <div id="paymentMessage"></div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-4 px-6 rounded-lg hover:bg-green-700 transition duration-200 font-semibold text-lg">
                            PURCHASE & RENEW LICENSE
                        </button>
                    </form>
                </div>
            </div>

            <!-- Back to Dashboard -->
            <div class="mt-8 text-center">
                <a href="#" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Option selection
        function selectOption(option) {
            const manualOption = document.getElementById('manualOption');
            const paymentOption = document.getElementById('paymentOption');
            const manualForm = document.getElementById('manualForm');
            const paymentForm = document.getElementById('paymentForm');
            const manualRadio = document.getElementById('manual_radio');
            const paymentRadio = document.getElementById('payment_radio');

            // Reset all options
            manualOption.classList.remove('border-blue-500', 'bg-blue-50');
            paymentOption.classList.remove('border-green-500', 'bg-green-50');
            manualForm.classList.add('hidden');
            paymentForm.classList.add('hidden');

            if (option === 'manual') {
                manualOption.classList.add('border-blue-500', 'bg-blue-50');
                manualForm.classList.remove('hidden');
                manualRadio.checked = true;
                paymentRadio.checked = false;
            } else if (option === 'payment') {
                paymentOption.classList.add('border-green-500', 'bg-green-50');
                paymentForm.classList.remove('hidden');
                paymentRadio.checked = true;
                manualRadio.checked = false;
            }
        }

        function showValidationResult(message, type) {
            const validationResult = document.getElementById('validationResult');
            const validationMessage = document.getElementById('validationMessage');
            
            validationResult.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-blue-100', 'text-blue-800');
            
            if (type === 'success') {
                validationResult.classList.add('bg-green-100', 'text-green-800');
            } else if (type === 'error') {
                validationResult.classList.add('bg-red-100', 'text-red-800');
            } else {
                validationResult.classList.add('bg-blue-100', 'text-blue-800');
            }
            
            validationMessage.textContent = message;
            validationResult.classList.remove('hidden');
        }

        function showPaymentResult(message, type) {
            const paymentResult = document.getElementById('paymentResult');
            const paymentMessage = document.getElementById('paymentMessage');
            
            paymentResult.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
            
            if (type === 'success') {
                paymentResult.classList.add('bg-green-100', 'text-green-800');
            } else {
                paymentResult.classList.add('bg-red-100', 'text-red-800');
            }
            
            paymentMessage.textContent = message;
            paymentResult.classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Payment method handling
            const cardRadio = document.querySelector('input[name="payment_method"][value="card"]');
            const momoRadio = document.querySelector('input[name="payment_method"][value="momo"]');
            const cardDetails = document.getElementById('cardDetails');
            const momoDetails = document.getElementById('momoDetails');
            const cardNumberInput = document.getElementById('card_number');
            const expiryDateInput = document.getElementById('expiry_date');
            const cvvInput = document.getElementById('cvv');
            const momoNumberInput = document.getElementById('momo_number');

            // Payment method selection
            function togglePaymentDetails() {
                if (cardRadio.checked) {
                    cardDetails.classList.remove('hidden');
                    momoDetails.classList.add('hidden');
                } else if (momoRadio.checked) {
                    cardDetails.classList.add('hidden');
                    momoDetails.classList.remove('hidden');
                }
            }

            cardRadio.addEventListener('change', togglePaymentDetails);
            momoRadio.addEventListener('change', togglePaymentDetails);

            // Card number formatting
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                if (formattedValue.length > 19) formattedValue = formattedValue.substring(0, 19);
                e.target.value = formattedValue;
            });

            // Expiry date formatting
            expiryDateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });

            // CVV input (numbers only)
            cvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });

            // Mobile number formatting
            momoNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9+]/g, '');
                if (!value.startsWith('+250') && value.length > 0 && !value.startsWith('+')) {
                    value = '+250' + value;
                }
                e.target.value = value;
            });

            // Duration selection handling
            const durationOptions = document.querySelectorAll('input[name="duration"]');
            const orderSummary = document.getElementById('orderSummary');
            
            durationOptions.forEach(option => {
                option.addEventListener('change', function() {
                    // Update visual selection
                    document.querySelectorAll('.duration-selection').forEach(el => {
                        el.classList.remove('border-green-500', 'bg-green-50');
                    });
                    
                    if (this.checked) {
                        this.parentElement.querySelector('.duration-selection').classList.add('border-green-500', 'bg-green-50');
                        updateOrderSummary();
                    }
                });
            });
            
            function updateOrderSummary() {
                const selectedDuration = document.querySelector('input[name="duration"]:checked');
                if (selectedDuration) {
                    const price = selectedDuration.dataset.price;
                    const duration = selectedDuration.value;
                    
                    const durationText = {
                        '30': '1 Month (30 Days)',
                        '90': '3 Months (90 Days)', 
                        '180': '6 Months (180 Days)',
                        '365': '1 Year (365 Days)'
                    }[duration];
                    
                    document.getElementById('summaryDuration').textContent = durationText;
                    document.getElementById('summaryPrice').textContent = '$' + price;
                    document.getElementById('totalPrice').textContent = '$' + price;
                    orderSummary.classList.remove('hidden');
                } else {
                    orderSummary.classList.add('hidden');
                }
            }

            // Manual form submission
            const manualForm = document.getElementById('manualRenewalForm');
            if (manualForm) {
                manualForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const key = document.getElementById('license_key').value.trim();
                    showValidationResult('Processing license renewal...', 'loading');
                    
                    fetch('/license/renew-manual', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            license_key: key,
                            company_id: '{{ $license->company_id }}'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showValidationResult(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = '/';
                            }, 2000);
                        } else {
                            showValidationResult(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showValidationResult('Network error. Please try again.', 'error');
                    });
                });
            }

            // Payment form submission
            const paymentForm = document.getElementById('paymentRenewalForm');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get selected duration
                    const durationOption = document.querySelector('input[name="duration"]:checked');
                    if (!durationOption) {
                        showPaymentResult('Please select a license duration', 'error');
                        return;
                    }
                    const durationDays = durationOption.value;
                    
                    // Get payment method
                    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                    if (!paymentMethod) {
                        showPaymentResult('Please select a payment method', 'error');
                        return;
                    }
                    
                    showPaymentResult('Processing payment and renewing license...', 'loading');
                    
                    let formData = {
                        duration_days: durationDays,
                        payment_method: paymentMethod.value
                    };
                    
                    // Add payment details based on method
                    if (paymentMethod.value === 'card') {
                        formData.card_number = document.getElementById('card_number').value;
                        formData.expiry_date = document.getElementById('expiry_date').value;
                        formData.cvv = document.getElementById('cvv').value;
                        formData.card_name = document.getElementById('card_name').value;
                    } else if (paymentMethod.value === 'momo') {
                        const momoProvider = document.querySelector('input[name="momo_provider"]:checked');
                        if (!momoProvider) {
                            showPaymentResult('Please select a mobile money provider', 'error');
                            return;
                        }
                        formData.momo_provider = momoProvider.value;
                        formData.momo_number = document.getElementById('momo_number').value;
                    }
                    
                    fetch('/license/renew-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showPaymentResult(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = '/';
                            }, 2000);
                        } else {
                            showPaymentResult(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showPaymentResult('Network error. Please try again.', 'error');
                    });
                });
            }
        });
        
    </script>
</body>
</html>