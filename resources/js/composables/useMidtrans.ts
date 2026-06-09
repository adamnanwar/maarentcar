import { ref } from 'vue'

declare global {
  interface Window {
    snap: {
      pay: (
        token: string,
        options: {
          onSuccess?: (result: unknown) => void
          onPending?: (result: unknown) => void
          onError?: (result: unknown) => void
          onClose?: () => void
        }
      ) => void
    }
  }
}

type PaymentResult = 'success' | 'pending' | 'error' | 'close'

export function useMidtrans() {
  const isLoading = ref(false)
  const isScriptLoaded = ref(false)

  function loadSnapScript(): Promise<boolean> {
    return new Promise((resolve) => {
      if (window.snap) {
        isScriptLoaded.value = true
        resolve(true)
        return
      }

      // Check if script is already being loaded
      const existingScript = document.querySelector(
        'script[src*="snap.js"]'
      ) as HTMLScriptElement

      if (existingScript) {
        existingScript.onload = () => {
          isScriptLoaded.value = true
          resolve(true)
        }
        return
      }

      const script = document.createElement('script')
      const clientKey = import.meta.env.VITE_MIDTRANS_CLIENT_KEY

      // Determine sandbox or production URL
      const isProduction = import.meta.env.VITE_MIDTRANS_IS_PRODUCTION === 'true'
      script.src = isProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js'

      script.setAttribute('data-client-key', clientKey)

      script.onload = () => {
        isScriptLoaded.value = true
        resolve(true)
      }

      script.onerror = () => {
        console.error('Failed to load Midtrans Snap script')
        resolve(false)
      }

      document.head.appendChild(script)
    })
  }

  async function pay(snapToken: string): Promise<PaymentResult> {
    isLoading.value = true

    try {
      const loaded = await loadSnapScript()

      if (!loaded || !window.snap) {
        console.error('Snap script not loaded')
        return 'error'
      }

      return new Promise((resolve) => {
        window.snap.pay(snapToken, {
          onSuccess: () => {
            isLoading.value = false
            resolve('success')
          },
          onPending: () => {
            isLoading.value = false
            resolve('pending')
          },
          onError: () => {
            isLoading.value = false
            resolve('error')
          },
          onClose: () => {
            isLoading.value = false
            resolve('close')
          },
        })
      })
    } catch (e) {
      console.error('Payment error:', e)
      isLoading.value = false
      return 'error'
    }
  }

  return {
    isLoading,
    isScriptLoaded,
    loadSnapScript,
    pay,
  }
}
