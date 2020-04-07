<template>
  <section>
    <div>
      <div>{{ rank }}</div>
      <h3>{{ islandName }}{{ keep }}{{ beginner }}</h3>
      <div>{{ monster }}{{ soccer }}</div>
      <div>{{ prize }}{{ viking }}</div>
      <div>{{ zins }}</div>
      <div>前ターン比</div>
    </div>
    <div>
      <dl class="properties">
        <div v-for="prop in properties" :key="prop.key">
          <dt>{{ prop.key }}</dt>
          <dd>{{ prop.value }}</dd>
        </div>
      </dl>
      <div class="p-1 text-left">items:</div>
      <div class="p-1 pt-0 text-left">{{ author }}: {{ comments }}</div>
    </div>
  </section>
</template>

<script lang="ts">
import Vue from 'vue'
export default Vue.extend({
  data() {
    return {
      rank: 1,
      islandName: 'foobar',
      properties: [
        { key: '得点', value: '7680192' },
        { key: '人口', value: '	3612000人' },
        { key: '領土', value: '138mi2' },
        { key: '天気', value: '晴れ☀' },
        { key: '資金', value: '推定100000億円' },
        { key: '食料', value: '9999900トン' },
        { key: '失業率', value: '-157%' },
        { key: '農場規模', value: '1038000人' },
        { key: '工場規模', value: '保有せず' },
        { key: '商業規模', value: '1786000人' },
        { key: '採掘場規模', value: '425000人' },
        { key: '発電所規模', value: '60540kW' },
        { key: '電力供給率', value: '570%' },
        { key: '人工衛星', value: '●●●●' }
      ],
      author: 'author',
      comments: 'commentscomments'
    }
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
