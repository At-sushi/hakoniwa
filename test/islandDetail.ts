// 美乳
import { IslandDetail } from '@/types'

const islandDetail: IslandDetail = {
  rank: 1,
  islandName: 'hogefuga',
  keep: true,
  beginner: true,
  monster: 1,
  soccer: {
    team: 1,
    matched: 10,
    won: 5,
    lose: 4,
    draw: 1,
    attack: 10,
    defense: 6,
    gotPoint: 8,
    lostPoint: 5
  },
  prizes: [
    {
      category: 'foobar',
      grade: 1,
      name: 'foobar_1'
    }
  ],
  viking: 1,
  zins: [
    {
      id: 'zin_1',
      name: 'baz_zin_1'
    }
  ],
  prevTurnRatio: {
    point: 10,
    population: -10,
    bill: 10,
    ration: -20
  },
  items: [
    {
      id: 'item_nya_1',
      name: 'item_nya',
      amount: 1
    },
    {
      id: 'item_nya_2',
      name: 'item_nya2',
      amount: 5
    }
  ],
  author: '',
  comment: 'foobarbaznya',
  point: 100,
  population: 10,
  territory: 30,
  weather: '☀️',
  bill: 1000,
  ration: 100,
  employmentRate: 60,
  agriculture: 10,
  industry: 10,
  commerse: 10,
  mine: 10,
  powerhouse: 10,
  powersupplyRate: 80,
  satelites: [
    {
      id: 'satelite_1',
      name: 'satelite_1',
      initialDurability: 60,
      durability: 40
    },
    {
      id: 'satelite_2',
      name: 'satelite_2',
      initialDurability: 60,
      durability: 40
    }
  ]
}

export default islandDetail
