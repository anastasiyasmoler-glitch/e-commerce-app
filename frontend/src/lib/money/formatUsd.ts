export function formatUsd(cents: number, currency = 'USD'): string {
  if (currency !== 'USD') {
    return `${(cents / 100).toFixed(2)} ${currency}`
  }

  return `$${(cents / 100).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}
