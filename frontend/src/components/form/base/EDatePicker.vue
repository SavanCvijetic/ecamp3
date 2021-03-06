<!--
Displays a field as a date picker (can be used with v-model)
-->

<template>
  <base-picker
    :icon="icon"
    :value="value"
    :format="format"
    :format-picker="formatPicker"
    :parse="parse"
    :parse-picker="parsePicker"
    v-bind="$attrs"
    @input="$emit('input', $event)">
    <template slot-scope="picker">
      <v-date-picker
        :value="picker.value || ''"
        :locale="$i18n.locale"
        first-day-of-week="1"
        no-title
        scrollable
        @input="picker.on.input">
        <v-spacer />
        <v-btn text color="primary"
               data-testid="action-cancel"
               @click="picker.on.close">
          {{ $tc('global.button.cancel') }}
        </v-btn>
        <v-btn text color="primary"
               data-testid="action-ok"
               @click="picker.on.save">
          {{ $tc('global.button.ok') }}
        </v-btn>
      </v-date-picker>
    </template>

    <!-- passing the append slot through -->
    <template #append>
      <slot name="append" />
    </template>
  </base-picker>
</template>

<script>
import BasePicker from './BasePicker'

export default {
  name: 'DatePicker',
  components: { BasePicker },
  props: {
    value: { type: [String, Number], required: true },
    icon: { type: String, required: false, default: 'mdi-calendar' },
    valueFormat: { type: [String, Array], default: 'YYYY-MM-DD' }
  },
  methods: {
    format (val) {
      if (val !== '') {
        return this.$date.utc(val, this.valueFormat).format('L')
      }
      return ''
    },
    formatPicker (val) {
      if (val !== '') {
        return this.$date.utc(val, this.valueFormat).format(this.$date.HTML5_FMT.DATE)
      }
      return ''
    },
    parse (val) {
      if (val) {
        const parsedDate = this.$date.utc(val, 'L')
        if (parsedDate.isValid() && parsedDate.format('L') === val) {
          return Promise.resolve(parsedDate.format(this.valueFormat))
        } else {
          return Promise.reject(new Error('invalid format'))
        }
      } else {
        return Promise.resolve('')
      }
    },
    parsePicker (val) {
      if (val) {
        const parsedDate = this.$date.utc(val, this.$date.HTML5_FMT.DATE)
        if (parsedDate.isValid() && parsedDate.format(this.$date.HTML5_FMT.DATE) === val) {
          return Promise.resolve(parsedDate.format(this.valueFormat))
        } else {
          return Promise.reject(new Error('invalid format'))
        }
      } else {
        return Promise.resolve('')
      }
    }
  }
}
</script>

<style scoped>
</style>
