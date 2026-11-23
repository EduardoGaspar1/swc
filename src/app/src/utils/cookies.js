export function getCookie(name) {
  const prefix = `${encodeURIComponent(name)}=`
  const parts = document.cookie.split(';')

  for (const part of parts) {
    const cookie = part.trim()
    if (cookie.startsWith(prefix)) {
      return decodeURIComponent(cookie.slice(prefix.length))
    }
  }

  return null
}

export function setCookie(name, value, { days = 7, path = '/', sameSite = 'Lax' } = {}) {
  const maxAge = Math.floor(days * 24 * 60 * 60)
  const secure = window.location.protocol === 'https:' ? '; Secure' : ''

  document.cookie = [
    `${encodeURIComponent(name)}=${encodeURIComponent(value)}`,
    `Path=${path}`,
    `Max-Age=${maxAge}`,
    `SameSite=${sameSite}`,
    secure,
  ]
    .filter(Boolean)
    .join('; ')
}

export function removeCookie(name, { path = '/' } = {}) {
  document.cookie = `${encodeURIComponent(name)}=; Path=${path}; Max-Age=0; SameSite=Lax`
}
