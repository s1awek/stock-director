<div class="wrap" id="wp-stock-director-settings">
  <h1><?php echo esc_html_e('Stock Status Settings', 'wp-stock-director'); ?></h1>
  <div class="conditions-wrap">
    <label for="minQuantity">
      <span><?php echo esc_html_e('Minimum Quantity', 'wp-stock-director'); ?></span>
      <input type="number" v-model="newCondition.minQuantity" readonly>
    </label>


    <label for="maxQuantity">
      <span>
        <?php echo esc_html_e('Maximum Quantity', 'wp-stock-director'); ?>
      </span>
      <input type="number" v-model="newCondition.maxQuantity" id="maxQuantity">
    </label>


    <label for="message">
      <span>
        <?php echo esc_html_e('Message', 'wp-stock-director'); ?>
      </span>
      <textarea v-model="newCondition.message"></textarea>
    </label>


    <button @click="addCondition" :disabled="!isValidNewCondition">
      <?php echo esc_html_e('Add Condition', 'wp-stock-director'); ?>
    </button>
  </div>

  <div v-for="(condition, index) in conditions" :key="index">
    <p>
      {{ condition.minQuantity }} - {{ condition.maxQuantity }}: {{ condition.message }}
      <button @click="removeCondition(index)"><?php echo esc_html_e('Remove', 'wp-stock-director'); ?></button>
    </p>
  </div>

  <button @click="saveConditions"><?php echo esc_html_e('Save Settings', 'wp-stock-director'); ?></button>
</div>