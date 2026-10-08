import { Heart, Search, ShoppingCart, Truck, User } from 'lucide-react'
import { FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { getDisplayName } from '../../auth/session'

const categories = [
  'Sofas',
  'Beds',
  'Tables',
  'Chairs',
  'Storage',
  'Lighting',
  'Textiles',
  'Decor',
  'Kitchen',
]

type StoreHeaderProps = {
  signedInName: string | null
  onSignIn: () => void
}

export function StoreHeader({ signedInName, onSignIn }: StoreHeaderProps) {
  const name = signedInName ?? getDisplayName()

  function onSearch(event: FormEvent) {
    event.preventDefault()
  }

  return (
    <>
      <div className="topbar">
        <div className="container topbar-inner">
          <span className="topbar-item">
            <Truck size={16} aria-hidden />
            Free shipping on orders over $50
          </span>
        </div>
      </div>
      <header className="header">
        <div className="container header-inner">
          <Link to="/" className="logo">
            <span className="logo-mark">D</span>
            Domo
          </Link>
          <form className="search" role="search" onSubmit={onSearch}>
            <select className="search-select" aria-label="Store filter">
              <option>All stores</option>
            </select>
            <input
              className="search-input"
              type="search"
              placeholder="Sofa, lamp, bedding..."
              aria-label="Search products"
            />
            <button className="search-btn" type="submit">
              <Search size={18} aria-hidden />
              Search
            </button>
          </form>
          <nav className="header-actions" aria-label="Account">
            {name ? (
              <span className="header-action">
                <User size={22} />
                <span className="t">{name}</span>
              </span>
            ) : (
              <button type="button" className="header-action" onClick={onSignIn}>
                <User size={22} />
                <span className="t">Sign in</span>
              </button>
            )}
            <span className="header-action">
              <Heart size={22} />
              <span className="t">Saved</span>
            </span>
            <span className="header-action">
              <ShoppingCart size={22} />
              <span className="t">Cart</span>
              <span className="header-action-badge">0</span>
            </span>
          </nav>
        </div>
      </header>
      <nav className="category-nav" aria-label="Categories">
        <div className="container category-nav-inner">
          {categories.map((label) => (
            <span key={label} className="category-link">
              {label}
            </span>
          ))}
          <span className="category-link sale">% Sale</span>
        </div>
      </nav>
    </>
  )
}
