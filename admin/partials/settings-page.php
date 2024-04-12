<div class="wrap" id="wp-stock-director-settings">
  <div class="wrap__inner" :class="{loading: loading}">
    <h1><?php echo esc_html_e('Stock Status Settings', 'wp-stock-director'); ?></h1>
    <h2 class=""><?php echo esc_html_e('Add new range', 'wp-stock-director'); ?></h2>
    <div class="conditions-wrap">
      <!-- Labels and fields -->
      <label for="minQuantity">
        <span><?php echo esc_html_e('Start Quantity', 'wp-stock-director'); ?></span>
        <input type="number" class="range-input" v-model="newCondition.minQuantity" readonly>
      </label>

      <label for="maxQuantity">
        <span><?php echo esc_html_e('End Quantity', 'wp-stock-director'); ?></span>
        <input type="number" class="range-input" v-model="newCondition.maxQuantity" id="maxQuantity">
      </label>

      <label for="message">
        <span><?php echo esc_html_e('Stock Message', 'wp-stock-director'); ?></span>
        <input class="message-input" type="text" v-model="newCondition.message">
      </label>

      <!-- Buttons -->
      <button class="btn btn-secondary" @click="addCondition" :disabled="!isValidNewCondition">
        <?php echo esc_html_e('Add', 'wp-stock-director'); ?>
      </button>
    </div>

    <ul class="conditions">
      <!-- Existing conditions list -->
      <li v-for="(condition, index) in conditions" :key="index" class="conditions__item">
        <div class="conditions__item-inner">
          <span class="label">From:</span><span class="value">{{ condition.minQuantity }}</span><span class="label">To:</span><span class="value">{{ condition.maxQuantity }}</span><span class="label">Stock message:</span><span class="value message">{{ condition.message }}</span>
          <button id="saveSettings" class="btn btn-secondary" @click="removeCondition(index)"><?php echo esc_html_e('Delete Range', 'wp-stock-director'); ?></button>
        </div>
      </li>
    </ul>

    <!-- Save button -->
    <button class="btn btn-primary" :disabled="loading || !conditionsChanged" @click="saveConditions">
      <?php echo esc_html_e('Update Inventory Conditions', 'wp-stock-director'); ?>
    </button>
  </div>
</div>