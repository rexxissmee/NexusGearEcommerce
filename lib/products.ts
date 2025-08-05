export interface Product {
  id: string
  name: string
  price: number
  originalPrice?: number
  image: string
  category: string
  rating: number
  reviews: number
  featured?: boolean
  sale?: boolean
  newArrival?: boolean
}

export const products: Product[] = [
  // PC Handhelds
  {
    id: "1",
    name: "NexusDeck Pro Gaming Handheld",
    price: 649.99,
    originalPrice: 799.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "PC Handheld",
    rating: 4.8,
    reviews: 324,
    featured: true,
    sale: true,
  },
  {
    id: "2",
    name: "NexusDeck Lite Portable Gaming PC",
    price: 449.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "PC Handheld",
    rating: 4.6,
    reviews: 189,
    newArrival: true,
  },
  {
    id: "3",
    name: "NexusDeck OLED Gaming Handheld",
    price: 899.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "PC Handheld",
    rating: 4.9,
    reviews: 156,
    featured: true,
    newArrival: true,
  },

  // Controllers
  {
    id: "4",
    name: "NexusControl Pro Wireless Controller",
    price: 79.99,
    originalPrice: 99.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Controller",
    rating: 4.7,
    reviews: 412,
    featured: true,
    sale: true,
  },
  {
    id: "5",
    name: "NexusControl Elite Racing Wheel",
    price: 299.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Controller",
    rating: 4.8,
    reviews: 203,
    featured: true,
  },
  {
    id: "6",
    name: "NexusControl Arcade Fight Stick",
    price: 159.99,
    originalPrice: 199.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Controller",
    rating: 4.6,
    reviews: 167,
    sale: true,
    newArrival: true,
  },

  // Gaming Mice
  {
    id: "7",
    name: "VelocityMouse Pro Gaming Mouse",
    price: 89.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Gaming Mouse",
    rating: 4.8,
    reviews: 534,
    featured: true,
  },
  {
    id: "8",
    name: "VelocityMouse Wireless Ultra",
    price: 129.99,
    originalPrice: 159.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Gaming Mouse",
    rating: 4.7,
    reviews: 298,
    sale: true,
    newArrival: true,
  },
  {
    id: "9",
    name: "VelocityMouse Lightweight Esports",
    price: 69.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Gaming Mouse",
    rating: 4.6,
    reviews: 445,
    featured: true,
  },
  {
    id: "10",
    name: "VelocityMouse RGB Gaming Mouse",
    price: 59.99,
    originalPrice: 79.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Gaming Mouse",
    rating: 4.4,
    reviews: 321,
    sale: true,
  },

  // Accessories
  {
    id: "11",
    name: "PrecisionPad XL Gaming Mousepad",
    price: 29.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Accessories",
    rating: 4.5,
    reviews: 142,
    featured: true,
  },
  {
    id: "12",
    name: "RGB Keycap Set - Cosmic",
    price: 39.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Accessories",
    rating: 4.6,
    reviews: 87,
    newArrival: true,
  },
  {
    id: "13",
    name: "NexusStand Pro Monitor Arm",
    price: 149.99,
    originalPrice: 199.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Accessories",
    rating: 4.7,
    reviews: 234,
    sale: true,
  },
  {
    id: "14",
    name: "GamerDesk RGB LED Strip Kit",
    price: 24.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Accessories",
    rating: 4.3,
    reviews: 156,
    newArrival: true,
  },
  {
    id: "15",
    name: "NexusCharge Wireless Charging Pad",
    price: 49.99,
    originalPrice: 69.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Accessories",
    rating: 4.4,
    reviews: 98,
    sale: true,
  },
  {
    id: "16",
    name: "ProGrip Controller Stand Set",
    price: 19.99,
    image: "/placeholder.svg?height=300&width=300",
    category: "Accessories",
    rating: 4.2,
    reviews: 76,
  },
]

// Helper functions to get products by category
export const getProductsByCategory = (category: string): Product[] => {
  return products.filter((product) => product.category === category)
}

export const getFeaturedProducts = (): Product[] => {
  return products.filter((product) => product.featured)
}

export const getNewArrivals = (): Product[] => {
  return products.filter((product) => product.newArrival)
}

export const getSaleProducts = (): Product[] => {
  return products.filter((product) => product.sale)
}

export const categories = ["PC Handheld", "Controller", "Gaming Mouse", "Accessories"]

export const filterProducts = (
  products: Product[],
  filters: {
    category?: string
    featured?: boolean
    newArrival?: boolean
    sale?: boolean
    minPrice?: number
    maxPrice?: number
    minRating?: number
  },
): Product[] => {
  return products.filter((product) => {
    if (filters.category && product.category !== filters.category) return false
    if (filters.featured && !product.featured) return false
    if (filters.newArrival && !product.newArrival) return false
    if (filters.sale && !product.sale) return false
    if (filters.minPrice && product.price < filters.minPrice) return false
    if (filters.maxPrice && product.price > filters.maxPrice) return false
    if (filters.minRating && product.rating < filters.minRating) return false
    return true
  })
}

export const sortProducts = (products: Product[], sortBy: string): Product[] => {
  const sorted = [...products]

  switch (sortBy) {
    case "price-low":
      return sorted.sort((a, b) => a.price - b.price)
    case "price-high":
      return sorted.sort((a, b) => b.price - a.price)
    case "rating":
      return sorted.sort((a, b) => b.rating - a.rating)
    case "reviews":
      return sorted.sort((a, b) => b.reviews - a.reviews)
    case "name":
      return sorted.sort((a, b) => a.name.localeCompare(b.name))
    default:
      return sorted
  }
}
