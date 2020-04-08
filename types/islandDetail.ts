export interface IslandDetail {
  rank: number
  islandName: string
  keep: boolean
  beginner: boolean
  monster: number
  soccer: SoccerDetail | null
  prizes: Prize[] | null
  viking: number
  zins: Zin[] | null
  prevTurnRatio: {
    point: number
    population: number
    bill: number
    ration: number
  }
  items: Item[] | null
  author: string
  comment: string
  point: number
  population: number
  territory: number
  weather: Weather
  bill: number
  ration: number
  employmentRate: number
  agriculture: number
  industry: number
  commerse: number
  mine: number
  powerhouse: number
  powersupplyRate: number
  satelites: Satelite[]
}

interface SoccerDetail {
  team: number
  matched: number
  won: number
  lose: number
  draw: number
  attack: number
  defense: number
  gotPoint: number
  lostPoint: number
}

interface Prize {
  category: string
  grade: number
  name: string
}

interface Zin {
  id: string
  name: string
}

interface Item {
  id: string
  name: string
  amount: number
}

export interface Satelite {
  id: string
  name: string
  initialDurability: number
  durability: number
}

type Weather = '☀️' | '☁️' | '🌫️' | '🌪️' | '☔' | '❄️' | '🌩️'
