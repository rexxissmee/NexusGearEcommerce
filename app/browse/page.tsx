"use client"
import { useState, useEffect, useMemo } from "react"
import { useSearchParams } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet"
import { Checkbox } from "@/components/ui/checkbox"
import { Slider } from "@/components/ui/slider"
import { Search, Filter, Star, X } from "lucide-react"
import ProductCard from "@/components/product-card"
import { products, categories, filterProducts, sortProducts } from "@/lib/products"

export default function BrowsePage() {
  const searchParams = useSearchParams()
  const [searchTerm, setSearchTerm] = useState("")
  const [sortBy, setSortBy] = useState("default")
  const [priceRange, setPriceRange] = useState([0, 1000])
  const [minRating, setMinRating] = useState(0)
  const [selectedCategories, setSelectedCategories] = useState<string[]>([])
  const [showFeatured, setShowFeatured] = useState(false)
  const [showNewArrivals, setShowNewArrivals] = useState(false)
  const [showSale, setShowSale] = useState(false)

  // Initialize filters from URL parameters
  useEffect(() => {
    const category = searchParams.get("category")
    const featured = searchParams.get("featured")
    const newArrival = searchParams.get("newArrival")
    const sale = searchParams.get("sale")

    if (category) {
      setSelectedCategories([category])
    }
    if (featured === "true") {
      setShowFeatured(true)
    }
    if (newArrival === "true") {
      setShowNewArrivals(true)
    }
    if (sale === "true") {
      setShowSale(true)
    }
  }, [searchParams])

  const filteredAndSortedProducts = useMemo(() => {
    let filtered = products

    // Apply search filter
    if (searchTerm) {
      filtered = filtered.filter(
        (product) =>
          product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          product.category.toLowerCase().includes(searchTerm.toLowerCase()),
      )
    }

    // Apply filters
    filtered = filterProducts(filtered, {
      category: selectedCategories.length === 1 ? selectedCategories[0] : undefined,
      featured: showFeatured || undefined,
      newArrival: showNewArrivals || undefined,
      sale: showSale || undefined,
      minPrice: priceRange[0],
      maxPrice: priceRange[1],
      minRating: minRating,
    })

    // Apply category filter for multiple categories
    if (selectedCategories.length > 1) {
      filtered = filtered.filter((product) => selectedCategories.includes(product.category))
    }

    // Apply sorting
    return sortProducts(filtered, sortBy)
  }, [searchTerm, selectedCategories, showFeatured, showNewArrivals, showSale, priceRange, minRating, sortBy])

  const handleCategoryChange = (category: string, checked: boolean) => {
    if (checked) {
      setSelectedCategories((prev) => [...prev, category])
    } else {
      setSelectedCategories((prev) => prev.filter((c) => c !== category))
    }
  }

  const clearFilters = () => {
    setSelectedCategories([])
    setShowFeatured(false)
    setShowNewArrivals(false)
    setShowSale(false)
    setPriceRange([0, 1000])
    setMinRating(0)
    setSearchTerm("")
    setSortBy("default")
  }

  const activeFiltersCount =
    selectedCategories.length +
    (showFeatured ? 1 : 0) +
    (showNewArrivals ? 1 : 0) +
    (showSale ? 1 : 0) +
    (priceRange[0] > 0 || priceRange[1] < 1000 ? 1 : 0) +
    (minRating > 0 ? 1 : 0)

  const FilterContent = () => (
    <div className="space-y-6">
      {/* Categories */}
      <div>
        <h3 className="font-semibold mb-3">Categories</h3>
        <div className="space-y-2">
          {categories.map((category) => (
            <div key={category} className="flex items-center space-x-2">
              <Checkbox
                id={category}
                checked={selectedCategories.includes(category)}
                onCheckedChange={(checked) => handleCategoryChange(category, checked as boolean)}
              />
              <label
                htmlFor={category}
                className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
              >
                {category}
              </label>
            </div>
          ))}
        </div>
      </div>

      {/* Special Filters */}
      <div>
        <h3 className="font-semibold mb-3">Special</h3>
        <div className="space-y-2">
          <div className="flex items-center space-x-2">
            <Checkbox id="featured" checked={showFeatured} onCheckedChange={setShowFeatured} />
            <label htmlFor="featured" className="text-sm font-medium">
              Featured Products
            </label>
          </div>
          <div className="flex items-center space-x-2">
            <Checkbox id="newArrivals" checked={showNewArrivals} onCheckedChange={setShowNewArrivals} />
            <label htmlFor="newArrivals" className="text-sm font-medium">
              New Arrivals
            </label>
          </div>
          <div className="flex items-center space-x-2">
            <Checkbox id="sale" checked={showSale} onCheckedChange={setShowSale} />
            <label htmlFor="sale" className="text-sm font-medium">
              On Sale
            </label>
          </div>
        </div>
      </div>

      {/* Price Range */}
      <div>
        <h3 className="font-semibold mb-3">Price Range</h3>
        <div className="px-2">
          <Slider value={priceRange} onValueChange={setPriceRange} max={1000} min={0} step={10} className="w-full" />
          <div className="flex justify-between text-sm text-gray-500 mt-2">
            <span>${priceRange[0]}</span>
            <span>${priceRange[1]}</span>
          </div>
        </div>
      </div>

      {/* Rating */}
      <div>
        <h3 className="font-semibold mb-3">Minimum Rating</h3>
        <div className="space-y-2">
          {[4, 3, 2, 1].map((rating) => (
            <div key={rating} className="flex items-center space-x-2">
              <Checkbox
                id={`rating-${rating}`}
                checked={minRating === rating}
                onCheckedChange={(checked) => setMinRating(checked ? rating : 0)}
              />
              <label htmlFor={`rating-${rating}`} className="flex items-center text-sm">
                {Array.from({ length: rating }).map((_, i) => (
                  <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                ))}
                <span className="ml-1">& up</span>
              </label>
            </div>
          ))}
        </div>
      </div>

      {/* Clear Filters */}
      {activeFiltersCount > 0 && (
        <Button onClick={clearFilters} variant="outline" className="w-full bg-transparent">
          Clear All Filters ({activeFiltersCount})
        </Button>
      )}
    </div>
  )

  return (
    <div className="container px-4 py-8 mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-4">Browse Products</h1>

        {/* Search and Sort */}
        <div className="flex flex-col md:flex-row gap-4 items-center justify-between">
          <div className="relative w-full md:w-96">
            <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Search products..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>

          <div className="flex items-center gap-4">
            <Select value={sortBy} onValueChange={setSortBy}>
              <SelectTrigger className="w-48">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="default">Default</SelectItem>
                <SelectItem value="price-low">Price: Low to High</SelectItem>
                <SelectItem value="price-high">Price: High to Low</SelectItem>
                <SelectItem value="rating">Highest Rated</SelectItem>
                <SelectItem value="reviews">Most Reviews</SelectItem>
                <SelectItem value="name">Name A-Z</SelectItem>
              </SelectContent>
            </Select>

            {/* Mobile Filter Button */}
            <Sheet>
              <SheetTrigger asChild>
                <Button variant="outline" className="md:hidden bg-transparent">
                  <Filter className="h-4 w-4 mr-2" />
                  Filters {activeFiltersCount > 0 && `(${activeFiltersCount})`}
                </Button>
              </SheetTrigger>
              <SheetContent side="left" className="w-[300px] sm:w-[400px]">
                <SheetHeader>
                  <SheetTitle>Filters</SheetTitle>
                  <SheetDescription>Filter products by category, price, rating and more.</SheetDescription>
                </SheetHeader>
                <div className="mt-6">
                  <FilterContent />
                </div>
              </SheetContent>
            </Sheet>
          </div>
        </div>

        {/* Active Filters */}
        {activeFiltersCount > 0 && (
          <div className="flex flex-wrap gap-2 mt-4">
            {selectedCategories.map((category) => (
              <Badge key={category} variant="secondary" className="flex items-center gap-1">
                {category}
                <X className="h-3 w-3 cursor-pointer" onClick={() => handleCategoryChange(category, false)} />
              </Badge>
            ))}
            {showFeatured && (
              <Badge variant="secondary" className="flex items-center gap-1">
                Featured
                <X className="h-3 w-3 cursor-pointer" onClick={() => setShowFeatured(false)} />
              </Badge>
            )}
            {showNewArrivals && (
              <Badge variant="secondary" className="flex items-center gap-1">
                New Arrivals
                <X className="h-3 w-3 cursor-pointer" onClick={() => setShowNewArrivals(false)} />
              </Badge>
            )}
            {showSale && (
              <Badge variant="secondary" className="flex items-center gap-1">
                On Sale
                <X className="h-3 w-3 cursor-pointer" onClick={() => setShowSale(false)} />
              </Badge>
            )}
          </div>
        )}
      </div>

      <div className="flex gap-8">
        {/* Desktop Sidebar */}
        <div className="hidden md:block w-64 flex-shrink-0">
          <div className="sticky top-24 bg-white dark:bg-gray-900 p-6 rounded-lg border">
            <h2 className="font-semibold mb-4">Filters</h2>
            <FilterContent />
          </div>
        </div>

        {/* Products Grid */}
        <div className="flex-1">
          <div className="mb-4 text-sm text-gray-600">
            Showing {filteredAndSortedProducts.length} of {products.length} products
          </div>

          {filteredAndSortedProducts.length === 0 ? (
            <div className="text-center py-12">
              <p className="text-gray-500 mb-4">No products found matching your criteria.</p>
              <Button onClick={clearFilters} variant="outline">
                Clear Filters
              </Button>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {filteredAndSortedProducts.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
