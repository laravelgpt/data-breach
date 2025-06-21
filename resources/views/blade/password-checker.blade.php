@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">🔐 Password Security Checker</h2>
        <p class="text-gray-600">Check if your password has been compromised and analyze its strength</p>
    </div>

    <!-- Password Input -->
    <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Enter Password to Check
        </label>
        <div class="flex gap-2">
            <input 
                type="password" 
                id="password"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter your password..."
            >
            <button 
                id="checkButton"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
                <span id="checkText">🔍 Check</span>
                <span id="checkingText" class="hidden">Checking...</span>
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="mb-6 hidden">
        <div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Checking password security...</span>
        </div>
    </div>

    <!-- Results -->
    <div id="results" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Breach Status -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <span id="breachIcon" class="text-2xl mr-3"></span>
                    <h3 id="breachTitle" class="text-xl font-semibold"></h3>
                </div>
                
                <div id="breachDetails"></div>
            </div>

            <!-- Strength Analysis -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-4">Password Strength</h3>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Strength Level</span>
                        <span id="strengthLevel" class="font-medium"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="strengthBar" class="h-2 rounded-full transition-all duration-300"></div>
                    </div>
                    <p id="strengthScore" class="text-xs text-gray-500 mt-1"></p>
                </div>

                <div id="strengthFeedback" class="space-y-1 hidden">
                    <p class="text-sm font-medium text-gray-700">Suggestions:</p>
                    <ul id="feedbackList" class="text-sm text-gray-600 space-y-1"></ul>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div id="recommendations" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 hidden">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">🔒 Security Recommendations</h3>
            <ul id="recommendationsList" class="space-y-2"></ul>
        </div>
    </div>

    <!-- Passkey Generator -->
    <div class="border-t pt-6">
        <h3 class="text-xl font-semibold mb-4">🔑 Generate Secure Passkeys</h3>
        
        <div class="flex gap-3 mb-4">
            <button 
                id="generatePasskeyBtn"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500"
            >
                Generate Passkey
            </button>
            <button 
                id="generatePassphraseBtn"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500"
            >
                Generate Passphrase
            </button>
        </div>

        <div id="generatedPasskey" class="bg-gray-50 rounded-lg p-6 hidden">
            <h4 id="passkeyTitle" class="font-semibold mb-3"></h4>
            
            <div class="flex items-center gap-3 mb-3">
                <input 
                    id="passkeyInput"
                    type="text" 
                    readonly
                    class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm"
                >
                <button 
                    id="copyPasskeyBtn"
                    class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                >
                    📋 Copy
                </button>
            </div>

            <div id="passkeyStrength" class="text-sm text-gray-600 hidden">
                <p>Strength: <span id="passkeyStrengthLevel" class="font-medium"></span></p>
                <p>Entropy: <span id="passkeyEntropy" class="font-medium"></span></p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div id="flashMessage" class="fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg hidden"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const checkButton = document.getElementById('checkButton');
    const checkText = document.getElementById('checkText');
    const checkingText = document.getElementById('checkingText');
    const loadingState = document.getElementById('loadingState');
    const results = document.getElementById('results');
    const generatePasskeyBtn = document.getElementById('generatePasskeyBtn');
    const generatePassphraseBtn = document.getElementById('generatePassphraseBtn');
    const generatedPasskey = document.getElementById('generatedPasskey');
    const flashMessage = document.getElementById('flashMessage');

    // Check password
    async function checkPassword() {
        const password = passwordInput.value.trim();
        if (!password) return;

        setLoading(true);

        try {
            const response = await fetch('/api/data-breach/password/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ password }),
            });

            const data = await response.json();

            if (data.success) {
                displayResults(data.data);
            } else {
                showFlashMessage(data.message, 'error');
            }
        } catch (error) {
            showFlashMessage('Error checking password: ' + error.message, 'error');
        }

        setLoading(false);
    }

    // Generate passkey
    async function generatePasskey() {
        try {
            const response = await fetch('/api/data-breach/generate/passkey', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });

            const data = await response.json();

            if (data.success) {
                displayGeneratedPasskey(data.data, 'Passkey');
            } else {
                showFlashMessage(data.message, 'error');
            }
        } catch (error) {
            showFlashMessage('Error generating passkey: ' + error.message, 'error');
        }
    }

    // Generate passphrase
    async function generatePassphrase() {
        try {
            const response = await fetch('/api/data-breach/generate/passphrase', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });

            const data = await response.json();

            if (data.success) {
                displayGeneratedPasskey(data.data, 'Passphrase');
            } else {
                showFlashMessage(data.message, 'error');
            }
        } catch (error) {
            showFlashMessage('Error generating passphrase: ' + error.message, 'error');
        }
    }

    // Display results
    function displayResults(data) {
        // Breach status
        const breachIcon = document.getElementById('breachIcon');
        const breachTitle = document.getElementById('breachTitle');
        const breachDetails = document.getElementById('breachDetails');

        if (data.compromised) {
            breachIcon.textContent = '🔓';
            breachTitle.textContent = 'Password Compromised';
            breachTitle.className = 'text-xl font-semibold text-red-600';
            breachDetails.innerHTML = `
                <div class="space-y-2">
                    <p class="text-red-600 font-medium">
                        This password has been found in ${data.breach_count} data breaches
                    </p>
                    <p class="text-sm text-gray-600">
                        Sources: ${data.sources.join(', ')}
                    </p>
                </div>
            `;
        } else {
            breachIcon.textContent = '🔒';
            breachTitle.textContent = 'Password Safe';
            breachTitle.className = 'text-xl font-semibold text-green-600';
            breachDetails.innerHTML = `
                <p class="text-green-600 font-medium">
                    This password has not been found in any known data breaches
                </p>
            `;
        }

        // Strength analysis
        const strengthLevel = document.getElementById('strengthLevel');
        const strengthBar = document.getElementById('strengthBar');
        const strengthScore = document.getElementById('strengthScore');
        const strengthFeedback = document.getElementById('strengthFeedback');
        const feedbackList = document.getElementById('feedbackList');

        const strength = data.strength;
        const level = formatStrengthLevel(strength.level);
        const score = strength.score;
        const percentage = Math.min(100, (score / 8) * 100);

        strengthLevel.textContent = level;
        strengthLevel.className = `font-medium ${getStrengthColor(strength.level)}`;
        strengthBar.className = `h-2 rounded-full transition-all duration-300 ${getStrengthBgColor(strength.level)}`;
        strengthBar.style.width = percentage + '%';
        strengthScore.textContent = `Score: ${score}/8`;

        if (strength.feedback && strength.feedback.length > 0) {
            feedbackList.innerHTML = strength.feedback.map(feedback => 
                `<li class="flex items-start">
                    <span class="text-yellow-500 mr-2">•</span>
                    ${feedback}
                </li>`
            ).join('');
            strengthFeedback.classList.remove('hidden');
        } else {
            strengthFeedback.classList.add('hidden');
        }

        // Recommendations
        const recommendations = document.getElementById('recommendations');
        const recommendationsList = document.getElementById('recommendationsList');

        if (data.recommendations && data.recommendations.length > 0) {
            recommendationsList.innerHTML = data.recommendations.map(rec => 
                `<li class="flex items-start text-blue-800">
                    <span class="text-blue-500 mr-2">•</span>
                    ${rec}
                </li>`
            ).join('');
            recommendations.classList.remove('hidden');
        } else {
            recommendations.classList.add('hidden');
        }

        results.classList.remove('hidden');
    }

    // Display generated passkey
    function displayGeneratedPasskey(data, type) {
        const passkeyTitle = document.getElementById('passkeyTitle');
        const passkeyInput = document.getElementById('passkeyInput');
        const passkeyStrength = document.getElementById('passkeyStrength');
        const passkeyStrengthLevel = document.getElementById('passkeyStrengthLevel');
        const passkeyEntropy = document.getElementById('passkeyEntropy');

        passkeyTitle.textContent = `Generated ${type}`;
        passkeyInput.value = data.passkey || data.passphrase;

        if (data.strength) {
            passkeyStrengthLevel.textContent = formatStrengthLevel(data.strength.level);
            passkeyEntropy.textContent = data.strength.entropy.toFixed(1) + ' bits';
            passkeyStrength.classList.remove('hidden');
        } else {
            passkeyStrength.classList.add('hidden');
        }

        generatedPasskey.classList.remove('hidden');
    }

    // Copy to clipboard
    async function copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            showFlashMessage('Copied to clipboard!', 'success');
        } catch (error) {
            showFlashMessage('Failed to copy to clipboard', 'error');
        }
    }

    // Set loading state
    function setLoading(loading) {
        if (loading) {
            checkButton.disabled = true;
            checkText.classList.add('hidden');
            checkingText.classList.remove('hidden');
            loadingState.classList.remove('hidden');
        } else {
            checkButton.disabled = false;
            checkText.classList.remove('hidden');
            checkingText.classList.add('hidden');
            loadingState.classList.add('hidden');
        }
    }

    // Show flash message
    function showFlashMessage(message, type) {
        flashMessage.textContent = message;
        flashMessage.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        flashMessage.classList.remove('hidden');
        
        setTimeout(() => {
            flashMessage.classList.add('hidden');
        }, 3000);
    }

    // Helper functions
    function getStrengthColor(level) {
        const colors = {
            'very_strong': 'text-green-600',
            'strong': 'text-green-500',
            'medium': 'text-yellow-500',
            'weak': 'text-red-500',
        };
        return colors[level] || 'text-gray-500';
    }

    function getStrengthBgColor(level) {
        const colors = {
            'very_strong': 'bg-green-600',
            'strong': 'bg-green-500',
            'medium': 'bg-yellow-500',
            'weak': 'bg-red-500',
        };
        return colors[level] || 'bg-gray-500';
    }

    function formatStrengthLevel(level) {
        return level.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Event listeners
    checkButton.addEventListener('click', checkPassword);
    passwordInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') checkPassword();
    });
    generatePasskeyBtn.addEventListener('click', generatePasskey);
    generatePassphraseBtn.addEventListener('click', generatePassphrase);
    document.getElementById('copyPasskeyBtn').addEventListener('click', () => {
        copyToClipboard(document.getElementById('passkeyInput').value);
    });
});
</script>
@endsection 