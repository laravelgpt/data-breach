<template>
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
          v-model="password" 
          @keydown.enter="checkPassword"
          class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Enter your password..."
        >
        <button 
          @click="checkPassword"
          :disabled="isLoading"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="!isLoading">🔍 Check</span>
          <span v-else>Checking...</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="mb-6">
      <div class="flex items-center justify-center p-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">Checking password security...</span>
      </div>
    </div>

    <!-- Results -->
    <div v-if="!isLoading && password && breachResult">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Breach Status -->
        <div class="bg-gray-50 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <span class="text-2xl mr-3">{{ getBreachStatusIcon() }}</span>
            <h3 class="text-xl font-semibold" :class="getBreachStatusColor()">
              {{ breachResult.compromised ? 'Password Compromised' : 'Password Safe' }}
            </h3>
          </div>
          
          <div v-if="breachResult.compromised" class="space-y-2">
            <p class="text-red-600 font-medium">
              This password has been found in {{ breachResult.breach_count }} data breaches
            </p>
            <p class="text-sm text-gray-600">
              Sources: {{ breachResult.sources.join(', ') }}
            </p>
          </div>
          <div v-else>
            <p class="text-green-600 font-medium">
              This password has not been found in any known data breaches
            </p>
          </div>
        </div>

        <!-- Strength Analysis -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-xl font-semibold mb-4">Password Strength</h3>
          
          <div class="mb-4">
            <div class="flex justify-between text-sm mb-1">
              <span>Strength Level</span>
              <span class="font-medium" :class="getStrengthColor()">
                {{ formatStrengthLevel(strengthResult.level) }}
              </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-300"
                :class="getStrengthBgColor()"
                :style="{ width: Math.min(100, (strengthResult.score / 8) * 100) + '%' }"
              ></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Score: {{ strengthResult.score }}/8</p>
          </div>

          <div v-if="strengthResult.feedback && strengthResult.feedback.length" class="space-y-1">
            <p class="text-sm font-medium text-gray-700">Suggestions:</p>
            <ul class="text-sm text-gray-600 space-y-1">
              <li v-for="feedback in strengthResult.feedback" :key="feedback" class="flex items-start">
                <span class="text-yellow-500 mr-2">•</span>
                {{ feedback }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Recommendations -->
      <div v-if="recommendations && recommendations.length" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">🔒 Security Recommendations</h3>
        <ul class="space-y-2">
          <li v-for="recommendation in recommendations" :key="recommendation" class="flex items-start text-blue-800">
            <span class="text-blue-500 mr-2">•</span>
            {{ recommendation }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Passkey Generator -->
    <div class="border-t pt-6">
      <h3 class="text-xl font-semibold mb-4">🔑 Generate Secure Passkeys</h3>
      
      <div class="flex gap-3 mb-4">
        <button 
          @click="generatePasskey"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500"
        >
          Generate Passkey
        </button>
        <button 
          @click="generatePassphrase"
          class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500"
        >
          Generate Passphrase
        </button>
      </div>

      <div v-if="showPasskey && generatedPasskey" class="bg-gray-50 rounded-lg p-6">
        <h4 class="font-semibold mb-3">Generated {{ generatedPasskey.passkey ? 'Passkey' : 'Passphrase' }}</h4>
        
        <div class="flex items-center gap-3 mb-3">
          <input 
            type="text" 
            :value="generatedPasskey.passkey || generatedPasskey.passphrase" 
            readonly
            class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm"
          >
          <button 
            @click="copyToClipboard(generatedPasskey.passkey || generatedPasskey.passphrase)"
            class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            📋 Copy
          </button>
        </div>

        <div v-if="generatedPasskey.strength" class="text-sm text-gray-600">
          <p>Strength: <span class="font-medium">{{ formatStrengthLevel(generatedPasskey.strength.level) }}</span></p>
          <p>Entropy: <span class="font-medium">{{ generatedPasskey.strength.entropy.toFixed(1) }} bits</span></p>
        </div>
      </div>
    </div>

    <!-- Flash Messages -->
    <div v-if="flashMessage" class="fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg" :class="flashMessageClass">
      {{ flashMessage }}
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'

export default {
  name: 'PasswordChecker',
  setup() {
    const password = ref('')
    const isLoading = ref(false)
    const showPasskey = ref(false)
    const flashMessage = ref('')
    const flashMessageClass = ref('')

    const breachResult = reactive({
      compromised: false,
      breach_count: 0,
      sources: [],
      strength: {},
      recommendations: [],
    })

    const strengthResult = reactive({
      score: 0,
      level: 'weak',
      feedback: [],
    })

    const recommendations = ref([])
    const generatedPasskey = ref(null)

    const checkPassword = async () => {
      if (!password.value) return

      isLoading.value = true

      try {
        const response = await fetch('/api/data-breach/password/check', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          },
          body: JSON.stringify({ password: password.value }),
        })

        const data = await response.json()

        if (data.success) {
          Object.assign(breachResult, data.data)
          Object.assign(strengthResult, data.data.strength)
          recommendations.value = data.data.recommendations
        } else {
          showFlashMessage(data.message, 'error')
        }
      } catch (error) {
        showFlashMessage('Error checking password: ' + error.message, 'error')
      }

      isLoading.value = false
    }

    const generatePasskey = async () => {
      try {
        const response = await fetch('/api/data-breach/generate/passkey', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          },
        })

        const data = await response.json()

        if (data.success) {
          generatedPasskey.value = data.data
          showPasskey.value = true
        } else {
          showFlashMessage(data.message, 'error')
        }
      } catch (error) {
        showFlashMessage('Error generating passkey: ' + error.message, 'error')
      }
    }

    const generatePassphrase = async () => {
      try {
        const response = await fetch('/api/data-breach/generate/passphrase', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          },
        })

        const data = await response.json()

        if (data.success) {
          generatedPasskey.value = data.data
          showPasskey.value = true
        } else {
          showFlashMessage(data.message, 'error')
        }
      } catch (error) {
        showFlashMessage('Error generating passphrase: ' + error.message, 'error')
      }
    }

    const copyToClipboard = async (text) => {
      try {
        await navigator.clipboard.writeText(text)
        showFlashMessage('Copied to clipboard!', 'success')
      } catch (error) {
        showFlashMessage('Failed to copy to clipboard', 'error')
      }
    }

    const showFlashMessage = (message, type) => {
      flashMessage.value = message
      flashMessageClass.value = type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      
      setTimeout(() => {
        flashMessage.value = ''
      }, 3000)
    }

    const getBreachStatusIcon = () => {
      return breachResult.compromised ? '🔓' : '🔒'
    }

    const getBreachStatusColor = () => {
      return breachResult.compromised ? 'text-red-600' : 'text-green-600'
    }

    const getStrengthColor = () => {
      const colors = {
        'very_strong': 'text-green-600',
        'strong': 'text-green-500',
        'medium': 'text-yellow-500',
        'weak': 'text-red-500',
      }
      return colors[strengthResult.level] || 'text-gray-500'
    }

    const getStrengthBgColor = () => {
      const colors = {
        'very_strong': 'bg-green-600',
        'strong': 'bg-green-500',
        'medium': 'bg-yellow-500',
        'weak': 'bg-red-500',
      }
      return colors[strengthResult.level] || 'bg-gray-500'
    }

    const formatStrengthLevel = (level) => {
      return level.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
    }

    return {
      password,
      isLoading,
      showPasskey,
      flashMessage,
      flashMessageClass,
      breachResult,
      strengthResult,
      recommendations,
      generatedPasskey,
      checkPassword,
      generatePasskey,
      generatePassphrase,
      copyToClipboard,
      getBreachStatusIcon,
      getBreachStatusColor,
      getStrengthColor,
      getStrengthBgColor,
      formatStrengthLevel,
    }
  },
}
</script> 