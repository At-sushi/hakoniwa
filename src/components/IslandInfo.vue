<template>
  <section>
    <div>
      <div>{{ rank }}</div>
      <h3>
        {{ islandName }}
        <img v-if="keep" />
        <img v-if="beginner" />
      </h3>
      <div>
        <img v-if="monster > 0" />
        <img v-if="soccer !== null && soccer.team > 0" />
      </div>
      <div>
        <img v-if="prizes !== null" />
        <img v-if="soccer.team > 0" />
      </div>
      <div>
        <img v-if="zins !== null" />
      </div>
      <div>前ターン比</div>
    </div>
    <div>
      <dl class="properties">
        <div v-for="prop in properties" :key="prop.key">
          <dt>{{ prop.key }}</dt>
          <dd v-if="!Array.isArray(prop.value)">{{ prop.value }}</dd>
          <dd v-if="Array.isArray(prop.value)">
            <span v-for="p in prop.value" :key="p.id">
              {{ p.name }}
            </span>
          </dd>
        </div>
      </dl>
      <div class="p-1 text-left">items:</div>
      <div class="p-1 pt-0 text-left">{{ author }}: {{ comment }}</div>
    </div>
  </section>
</template>

<script lang="ts">
import Vue, { PropType } from 'vue'
import { IslandDetail } from '@/types'


export default Vue.extend({
  props: {
    rank: Number,
    islandName: String,
    keep: Boolean,
    beginner: Boolean,
    monster: Number,
    soccer: Object as PropType<IslandDetail['soccer']>,
    prizes: Array as PropType<IslandDetail['prizes']>,
    viking: Number,
    zins: Array as PropType<IslandDetail['zins']>,
    prevTurnRatio: Object as PropType<{
      point: number
      population: number
      bill: number
      ration: number
    }>,
    items: Array as PropType<IslandDetail['items']>,
    author: String,
    comment: String,
    properties: Array,
    point: Number,
    population: Number,
    territory: Number,
    weather: String as PropType<IslandDetail['weather']>,
    bill: Number,
    ration: Number,
    employmentRate: Number,
    agriculture: Number,
    industry: Number,
    commerse: Number,
    mine: Number,
    powerhouse: Number,
    powersupplyRate: Number,
    satelites: Array as PropType<IslandDetail['satelites']>
  }
})
</script>

<style lang="sass" scoped>
section
  @apply grid grid-cols-1 pb-4
  @screen sm
    @apply grid-cols-2
  div:nth-child(2)
    @apply grid
section + section
  @apply border-t pt-4

.properties
  @apply border-t border-l content-around flex flex-row flex-wrap items-stretch justify-between
  > div
    @apply border-r border-b flex-grow
  dt
    @apply border-b bg-gray-100 p-1 pb-0
  dd
    @apply p-1 pt-0
</style>
