<script>
import { ref, watch } from 'vue'

export default {
  name: 'FormInputCounter',
  props: {
    data: {
      type: Object,
      default: null
    },
    index: {
      type: String,
      default: null
    }
  },

  setup(props) {
    const inputValue = ref([0, 0])
    const updateInputValue = (type, counter, index) => {
      if (inputValue.value[index] >= 0 && inputValue.value[index] < Number(counter.max_val) ) {
        if (type === 'increment') {
          inputValue.value[index] += 1
        } else {
          inputValue.value[index] -= 1
        }
      } else {
        inputValue.value[index] = 0
      }
    }

    watch(inputValue, (arr) => {
      arr.forEach((val, valI) => {
        if (val > props.data.inputs[valI].max_val) {
          arr[valI] = Number(props.data.inputs[valI].max_val)
        } else if (val < 0) {
          arr[valI] = 0
        }
      })
    }, { deep: true})

    return {
      value: inputValue,
      updateInputValue
    }
  }
}
</script>


<template>
  <div class="msf-input-wrap">
    <div
      v-for="(counter, counterIndex) in data.inputs"
      :key="`Counter-${index}-input-${counterIndex}`"
      class="msf-input msf-input--counter"
    >
      <label
        :for="`Counter-${index}-input-${counterIndex}`"
        class="msf-input__label msf-input__label--counter c-input__label"
      >{{ counter.label }}
        <span
          v-if="counter.is_required"
          class="c-txt c-txt--highlight"
        >*</span></label>
      <div class="msf-input-wrap">
        <button
          class="c-btn c-btn--is-icon c-btn--has-icon msf-input__btn"
          @click="updateInputValue('decrement', counter, counterIndex)"
        >
          <span class="c-btn__label is-sr-only">Eine Person weniger</span>
          <svg class="c-btn__icon"><use xlink:href="#icon-minus" /></svg>
        </button>
        <input
          :id="`Counter-${index}-input-${counterIndex}`"
          v-model="value[counterIndex]"
          type="number"
          :max="counter.max_val"
          class="c-input__control msf-input__control msf-input__control--count"
          :required="counter.is_required"
        >
        <button
          class="c-btn c-btn--is-icon c-btn--has-icon msf-input__btn"
          @click="updateInputValue('increment', counter, counterIndex)"
        >
          <span class="c-btn__label is-sr-only">Eine Person mehr</span>
          <svg class="c-btn__icon"><use xlink:href="#icon-plus" /></svg>
        </button>
      </div>
    </div>
  </div>
</template>


<style>
input[type='number'] {
  -moz-appearance:textfield;
}
</style>