// import { IslandDetail } from '@/types/islandDetail'
import lang from '~/assets/ja.json'

type typeIslandDetail = keyof typeof lang.islandDetail

function format2ViewableStyleThatIslandDetail(arg: any) {
  const properties: { key: string; value: any }[] = []

  Object.keys(lang.islandDetail).forEach((k) => {
    properties.push({
      key: lang.islandDetail[<typeIslandDetail>k],
      value: arg[k as typeIslandDetail]
    })
  })

  return {
    rank: arg.rank,
    islandName: arg.islandName,
    keep: arg.keep,
    beginner: arg.beginner,
    monster: arg.monster,
    soccer: arg.soccer,
    prizes: arg.prizes,
    zins: arg.zins,
    properties,
    items: arg.items,
    author: arg.author,
    comment: arg.comment
  }
}

export default format2ViewableStyleThatIslandDetail
