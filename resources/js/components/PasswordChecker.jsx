import React, { useState } from 'react'

const PasswordChecker = () => {
  const [password, setPassword] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [showPasskey, setShowPasskey] = useState(false)
  const [flashMessage, setFlashMessage] = useState('')
  const [flashMessageClass, setFlashMessageClass] = useState('')

  const [breachResult, setBreachResult] = useState({
    compromised: false,
    breach_count: 0,
    sources: [],
    strength: {},
    recommendations: [],
  })

  const [strengthResult, setStrengthResult] = useState({
    score: 0,
    level: 'weak',
    feedback: [],
  })

  const [recommendations, setRecommendations] = useState([])
  const [generatedPasskey, setGeneratedPasskey] = useState(null)

  const checkPassword = async () => {
    if (!password) return

    setIsLoading(true)

    try {
      const response = await fetch('/api/data-breach/password/check', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        body: JSON.stringify({ password }),
      })

      const data = await response.json()

      if (data.success) {
        setBreachResult(data.data)
        setStrengthResult(data.data.strength)
        setRecommendations(data.data.recommendations)
      } else {
        showFlashMessage(data.message, 'error')
      }
    } catch (error) {
      showFlashMessage('Error checking password: ' + error.message, 'error')
    }

    setIsLoading(false)
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
        setGeneratedPasskey(data.data)
        setShowPasskey(true)
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
        setGeneratedPasskey(data.data)
        setShowPasskey(true)
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
    setFlashMessage(message)
    setFlashMessageClass(type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white')
    
    setTimeout(() => {
      setFlashMessage('')
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

  return (
    <div className="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
      <div className="mb-8">
        <h2 className="text-3xl font-bold text-gray-900 mb-2">🔐 Password Security Checker</h2>
        <p className="text-gray-600">Check if your password has been compromised and analyze its strength</p>
      </div>

      {/* Password Input */}
      <div className="mb-6">
        <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
          Enter Password to Check
        </label>
        <div className="flex gap-2">
          <input 
            type="password" 
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && checkPassword()}
            className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter your password..."
          />
          <button 
            onClick={checkPassword}
            disabled={isLoading}
            className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {!isLoading ? '🔍 Check' : 'Checking...'}
          </button>
        </div>
      </div>

      {/* Loading State */}
      {isLoading && (
        <div className="mb-6">
          <div className="flex items-center justify-center p-8">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span className="ml-3 text-gray-600">Checking password security...</span>
          </div>
        </div>
      )}

      {/* Results */}
      {!isLoading && password && breachResult && (
        <>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {/* Breach Status */}
            <div className="bg-gray-50 rounded-lg p-6">
              <div className="flex items-center mb-4">
                <span className="text-2xl mr-3">{getBreachStatusIcon()}</span>
                <h3 className={`text-xl font-semibold ${getBreachStatusColor()}`}>
                  {breachResult.compromised ? 'Password Compromised' : 'Password Safe'}
                </h3>
              </div>
              
              {breachResult.compromised ? (
                <div className="space-y-2">
                  <p className="text-red-600 font-medium">
                    This password has been found in {breachResult.breach_count} data breaches
                  </p>
                  <p className="text-sm text-gray-600">
                    Sources: {breachResult.sources.join(', ')}
                  </p>
                </div>
              ) : (
                <p className="text-green-600 font-medium">
                  This password has not been found in any known data breaches
                </p>
              )}
            </div>

            {/* Strength Analysis */}
            <div className="bg-gray-50 rounded-lg p-6">
              <h3 className="text-xl font-semibold mb-4">Password Strength</h3>
              
              <div className="mb-4">
                <div className="flex justify-between text-sm mb-1">
                  <span>Strength Level</span>
                  <span className={`font-medium ${getStrengthColor()}`}>
                    {formatStrengthLevel(strengthResult.level)}
                  </span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all duration-300 ${getStrengthBgColor()}`}
                    style={{ width: Math.min(100, (strengthResult.score / 8) * 100) + '%' }}
                  ></div>
                </div>
                <p className="text-xs text-gray-500 mt-1">Score: {strengthResult.score}/8</p>
              </div>

              {strengthResult.feedback && strengthResult.feedback.length > 0 && (
                <div className="space-y-1">
                  <p className="text-sm font-medium text-gray-700">Suggestions:</p>
                  <ul className="text-sm text-gray-600 space-y-1">
                    {strengthResult.feedback.map((feedback, index) => (
                      <li key={index} className="flex items-start">
                        <span className="text-yellow-500 mr-2">•</span>
                        {feedback}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          </div>

          {/* Recommendations */}
          {recommendations && recommendations.length > 0 && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
              <h3 className="text-lg font-semibold text-blue-900 mb-3">🔒 Security Recommendations</h3>
              <ul className="space-y-2">
                {recommendations.map((recommendation, index) => (
                  <li key={index} className="flex items-start text-blue-800">
                    <span className="text-blue-500 mr-2">•</span>
                    {recommendation}
                  </li>
                ))}
              </ul>
            </div>
          )}
        </>
      )}

      {/* Passkey Generator */}
      <div className="border-t pt-6">
        <h3 className="text-xl font-semibold mb-4">🔑 Generate Secure Passkeys</h3>
        
        <div className="flex gap-3 mb-4">
          <button 
            onClick={generatePasskey}
            className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500"
          >
            Generate Passkey
          </button>
          <button 
            onClick={generatePassphrase}
            className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500"
          >
            Generate Passphrase
          </button>
        </div>

        {showPasskey && generatedPasskey && (
          <div className="bg-gray-50 rounded-lg p-6">
            <h4 className="font-semibold mb-3">
              Generated {generatedPasskey.passkey ? 'Passkey' : 'Passphrase'}
            </h4>
            
            <div className="flex items-center gap-3 mb-3">
              <input 
                type="text" 
                value={generatedPasskey.passkey || generatedPasskey.passphrase} 
                readOnly
                className="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm"
              />
              <button 
                onClick={() => copyToClipboard(generatedPasskey.passkey || generatedPasskey.passphrase)}
                className="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
              >
                📋 Copy
              </button>
            </div>

            {generatedPasskey.strength && (
              <div className="text-sm text-gray-600">
                <p>Strength: <span className="font-medium">{formatStrengthLevel(generatedPasskey.strength.level)}</span></p>
                <p>Entropy: <span className="font-medium">{generatedPasskey.strength.entropy.toFixed(1)} bits</span></p>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Flash Messages */}
      {flashMessage && (
        <div className={`fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg ${flashMessageClass}`}>
          {flashMessage}
        </div>
      )}
    </div>
  )
}

export default PasswordChecker 