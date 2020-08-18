<template>
  <div>
    <div v-if="loading" class="spinner is-active wac_spinner"></div>
    <div v-else>
      <div v-if="$root.wac_form.type != 'product'">
        <input type="hidden" name="rulesLength" :value="conditions.length" />
        <div class="wac-form">
          <label>
            <strong>Conditions Relationship</strong>
            <div class="wac-checkbox">
              <label>
                <input name="wac_rule_relation" type="radio" value="match_all" v-model="relation" /> Match All
              </label>
              <label>
                <input name="wac_rule_relation" type="radio" value="match_any" v-model="relation" /> Match Any
              </label>
            </div>
          </label>
        </div>
        <div
          class="wac-flex wac-filter wac-bulk-discount"
          v-for="(condition, index) in conditions"
          :key="'condition'+index"
        >
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_rule_type_'+index">
                <strong>Condition Type</strong>
              </label>
              <select
                :id="'wac_rule_type_'+index"
                :name="'wac_rule_type_'+index"
                v-model="condition.type"
              >
                <option
                  v-for="(type, index) in types"
                  :key="'type-'+index"
                  :value="type.value"
                >{{ type.label }}</option>
              </select>
            </div>
          </div>
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_rule_operator_'+index">
                <strong>count should be</strong>
              </label>
              <select
                :id="'wac_rule_operator_'+index"
                :name="'wac_rule_operator_'+index"
                v-model="condition.operator"
              >
                <option
                  v-for="(operator, index) in operators"
                  :key="'operator-'+index"
                  :value="operator.value"
                >{{ operator.label }}</option>
              </select>
            </div>
          </div>
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_rule_item_'+index">
                <strong>item count</strong>
              </label>
              <input
                type="number"
                :id="'wac_rule_item_'+index"
                :name="'wac_rule_item_'+index"
                placeholder="1"
                min="1"
                v-model="condition.item_count"
              />
            </div>
          </div>
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_rule_calculate_'+index">
                <strong>calculate item count</strong>
              </label>
              <select
                :id="'wac_rule_calculate_'+index"
                :name="'wac_rule_calculate_'+index"
                v-model="condition.calculate"
              >
                <option
                  v-for="(calculate, index) in calculates"
                  :key="'calculate-'+index"
                  :value="calculate.value"
                >{{ calculate.label }}</option>
              </select>
            </div>
          </div>
          <div class="wac-filter-close">
            <span @click="removeRule(index)" class="dashicons dashicons-no-alt"></span>
          </div>
        </div>
        <div class="wac_buttons">
          <button type="button" @click="AddRules" class="button-primary">Add Condition</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "wacrules",
  data() {
    return {
      loading: true,
      relation: "match_all",
      types: [
        {
          label: "Subtotal",
          value: "cart_subtotal",
        },
        {
          label: "Line Item Count",
          value: "cart_line_items_count",
        },
      ],
      operators: [
        {
          label: "Less than ( < )",
          value: "less_than",
        },
        {
          label: "Less than or equal ( <= )",
          value: "less_than_or_equal",
        },
        {
          label: "Greater than or equal ( >= )",
          value: "greater_than_or_equal",
        },
        {
          label: "greater_than ( > )",
          value: "greater_than",
        },
      ],
      calculates: [
        {
          label: "Count all items in cart",
          value: "from_cart",
        },
        {
          label: "Only count items chosen in the filters set for this rule",
          value: "from_filter",
        },
      ],
      conditions: [
        // {
        //   type: "cart_subtotal",
        //   operator: "less_than",
        //   item_count: null,
        //   calculate: "from_cart",
        // },
      ],
    };
  },
  created() {
    this.getRules();
  },
  methods: {
    AddRules() {
      this.conditions.push({
        type: "cart_subtotal",
        operator: "less_than",
        item_count: null,
        calculate: "from_cart",
      });
    },
    removeRule(index) {
      this.conditions.splice(index, 1);
    },
    getRules() {
      this.loading = false;
      let formData = {
        action: "wac_get_rules",
        post_id: wac_post.id,
      };
      let root = this;
      axios
        .post(wac_helper_obj.ajax_url, Qs.stringify(formData))
        .then((response) => {
          if (response.data != [] && response.data != "") {
            root.relation = response.data.relation;
            root.conditions =
              response.data.rules == null ? [] : response.data.rules;
          }
          root.loading = false;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
  mounted() {
    this.getRules();
  },
};
</script>
