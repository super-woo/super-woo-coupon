<template>
  <div>
    <div v-if="loading" class="spinner is-active wac_spinner"></div>
    <div v-else>
      <div class="wac-flex wac-filter" v-if="$root.wac_form.type !== 'bulk'">
        <div class="wac-col-3">
          <div class="wac-form">
            <label for="wac_discount_type">
              <strong>Discount Type</strong>
            </label>
            <select id="wac_discount_type" name="wac_discount_type" v-model="discounts.type">
              <option value="percentage">Percentage discount</option>
              <option value="fixed">Fixed discount</option>
            </select>
          </div>
        </div>
        <div class="wac-filter-list">
          <div class="wac-form">
            <label for="wac_discount_value">
              <strong>Value</strong>
            </label>
            <input
              type="text"
              id="wac_discount_value"
              name="wac_discount_value"
              placeholder="0.00"
              v-model="discounts.value"
            />
          </div>
        </div>
      </div>
      <div v-else>
        <div
          class="wac-flex wac-filter wac-bulk-discount"
          v-for="(wacDiscount,index) in wacDiscounts"
          :key="'wacDiscount-'+index"
        >
          <input type="hidden" name="discountLength" :value="wacDiscounts.length" />
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_discount_min_'+index">
                <strong>Min</strong>
              </label>
              <input
                type="text"
                :id="'wac_discount_min_'+index"
                v-model="wacDiscount.min"
                :name="'wac_discount_min_'+index"
                placeholder="Min"
              />
            </div>
          </div>
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_discount_max_'+index">
                <strong>Max</strong>
              </label>
              <input
                type="text"
                :id="'wac_discount_max_'+index"
                v-model="wacDiscount.max"
                :name="'wac_discount_max_'+index"
                placeholder="Max"
              />
            </div>
          </div>
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_discount_type_'+index">
                <strong>Type</strong>
              </label>
              <select
                :id="'wac_discount_type_'+index"
                v-model="wacDiscount.type"
                :name="'wac_discount_type_'+index"
              >
                <option value="percentage">Percentage discount</option>
                <option value="fixed">Fixed discount</option>
              </select>
            </div>
          </div>
          <div class="wac-bulk-list">
            <div class="wac-form">
              <label :for="'wac_discount_value_'+index">
                <strong>Value</strong>
              </label>
              <input
                type="text"
                :id="'wac_discount_value_'+index"
                v-model="wacDiscount.value"
                :name="'wac_discount_value_'+index"
                placeholder="0.00"
              />
            </div>
          </div>
          <div class="wac-filter-close" v-if="wacDiscounts.length > 1">
            <span @click="removeRange(index)" class="dashicons dashicons-no-alt"></span>
          </div>
        </div>
        <div class="wac_buttons">
          <button @click="AddRange" type="button" class="button-primary">Add Range</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "wacdiscount",
  data() {
    return {
      loading: true,
      discounts: {
        type: "percentage",
        value: null,
      },
      wacDiscounts: [
        {
          min: null,
          max: null,
          type: "percentage",
          value: null,
        },
      ],
    };
  },
  created() {
    this.getDiscounts();
  },
  methods: {
    AddRange() {
      this.wacDiscounts.push({
        min: null,
        max: null,
        type: "percentage",
        value: null,
      });
    },
    removeRange(index) {
      this.wacDiscounts.splice(index, 1);
    },
    getDiscounts() {
      this.loading = true;
      let formData = {
        action: "wac_get_discounts",
        post_id: wac_post.id,
      };
      let root = this;
      axios
        .post(wac_helper_obj.ajax_url, Qs.stringify(formData))
        .then((response) => {
          if (response.data != [] && response.data != "") {
            if (root.$root.wac_form.type === "bulk") {
              root.wacDiscounts = response.data;
            } else {
              root.discounts = response.data;
            }
          }
          root.loading = false;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
  mounted() {
    this.getDiscounts();
  },
};
</script>
