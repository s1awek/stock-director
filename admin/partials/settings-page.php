<div class="wrap" id="wp-stock-director-settings">
  <h1><?php echo esc_html_e('Stock Status Settings', 'wp-stock-director'); ?></h1>
  <div class="conditions-wrap">
    <!-- Labels and fields -->
    <label for="minQuantity">
      <span><?php echo esc_html_e('Start Quantity', 'wp-stock-director'); ?></span>
      <input type="number" v-model="newCondition.minQuantity" readonly>
    </label>

    <label for="maxQuantity">
      <span><?php echo esc_html_e('End Quantity', 'wp-stock-director'); ?></span>
      <input type="number" v-model="newCondition.maxQuantity" id="maxQuantity">
    </label>

    <label for="message">
      <span><?php echo esc_html_e('Stock Message', 'wp-stock-director'); ?></span>
      <textarea v-model="newCondition.message"></textarea>
    </label>

    <!-- Buttons -->
    <button @click="addCondition" :disabled="!isValidNewCondition">
      <?php echo esc_html_e('Define New Range', 'wp-stock-director'); ?>
    </button>
  </div>

  <!-- Existing conditions list -->
  <div v-for="(condition, index) in conditions" :key="index" class="stock-condition">
    <p>
      {{ condition.minQuantity }} - {{ condition.maxQuantity }}: {{ condition.message }}
      <button @click="removeCondition(index)"><?php echo esc_html_e('Delete Range', 'wp-stock-director'); ?></button>
    </p>
  </div>

  <!-- Save button -->
  <button @click="saveConditions">
    <?php echo esc_html_e('Update Inventory Conditions', 'wp-stock-director'); ?>
  </button>
</div>