const { createApp, ref, computed, watch, nextTick } = Vue;

const app = createApp({
  setup() {
    // Reactive references for conditions and a new condition form
    const conditions = ref([]);
    const newCondition = ref({
      minQuantity: 1,
      maxQuantity: null,
      message: ''
    });

    watch(conditions, (currentConditions) => {
      if (currentConditions.length > 0) {
        const lastCondition = currentConditions[currentConditions.length - 1];
        newCondition.value.minQuantity = lastCondition.maxQuantity;
      }
    }, { deep: true });

    // Computed property to validate new condition
    const isValidNewCondition = computed(() => {
      const { minQuantity, maxQuantity, message } = newCondition.value;
      const isRangeValid = minQuantity !== null && maxQuantity !== null && parseInt(minQuantity) < parseInt(maxQuantity);
      const isMessageFilled = message.trim().length > 0;
      const isUniqueRange = conditions.value.length === 0 || conditions.value.every(condition =>
        maxQuantity > condition.maxQuantity
      );
      return isRangeValid && isMessageFilled && isUniqueRange;
    });

    // Method to add a new condition to the list
    const addCondition = () => {
      if (isValidNewCondition.value) {
        // Sort conditions before adding a new one
        conditions.value.push({ ...newCondition.value });
        conditions.value.sort((a, b) => a.minQuantity - b.minQuantity);

        // Update 'minQuantity' values of each condition to ensure they are in logical order
        conditions.value.forEach((condition, idx) => {
          if (idx > 0) {
            conditions.value[idx].minQuantity = conditions.value[idx - 1].maxQuantity;
          }
        });

        // Reset the new condition
        newCondition.value = { minQuantity: conditions.value[conditions.value.length - 1].maxQuantity, maxQuantity: null, message: '' };

        // Focus on the 'maxQuantity' field
        nextTick(() => {
          document.getElementById('maxQuantity').focus();
        });
      }
    };

    // Method to remove a condition from the list
    const removeCondition = (index) => {
      // Remove the selected condition
      conditions.value.splice(index, 1);

      // If there are no conditions left, reset the minQuantity for new condition to 1
      if (conditions.value.length === 0) {
        newCondition.value.minQuantity = 1;
      } else {
        // If the removed condition was not the last one, update the next condition's minQuantity
        if (index < conditions.value.length) {
          // Update the minQuantity of the next condition to the maxQuantity of the previous one
          conditions.value[index].minQuantity = conditions.value[index - 1].maxQuantity;
        }
      }
    };


    const saveConditions = async () => {
      // Wysyłanie danych do WordPressa przez AJAX
      try {
        const response = await fetch(mwsData.ajax_url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'save_conditions',
            nonce: mwsData.nonce,
            conditions: JSON.stringify(conditions.value),
          }),
        });

        if (!response.ok) {
          throw new Error('Network response was not ok.');
        }

        const responseData = await response.json();

        if (responseData.success) {
          // Obsługa sukcesu
          console.log('Settings saved:', responseData);
        } else {
          // Obsługa błędów związanych z WordPress
          console.error('Error from WP:', responseData);
        }
      } catch (error) {
        // Obsługa błędów związanych z siecią
        console.error('Fetch error:', error);
      }
    }
    // Expose the state and methods to the template
    return {
      conditions,
      newCondition,
      addCondition,
      removeCondition,
      saveConditions,
      isValidNewCondition
    };
  }
});

app.mount('#wp-stock-director-settings');