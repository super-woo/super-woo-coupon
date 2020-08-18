<template>
  <div>
    <div v-if="loading" class="spinner is-active wac_spinner"></div>
    <div v-else>
      <div
        class="wac-flex wac-filter"
        v-for="(wacfilter, index) in wacfilters"
        :key="'wacfilter-' + index"
      >
        <div class="wac-col-3">
          <div class="wac-form">
            <label for="wac_filter_type">
              <strong>Type</strong>
            </label>
            <select id="wac_filter_type" name="wac_filter_type[]" v-model="wacfilter.type">
              <option
                v-for="(filterType, index) in filterTypes"
                :key="'filterType-' + index"
                :value="filterType.value"
              >{{ filterType.label }}</option>
            </select>
          </div>
        </div>
        <div class="wac-filter-list" v-if="checkItemsAvaiable(wacfilter.type)">
          <div class="wac-form">
            <label for="wac_filter_lists">
              <strong>Lists Type</strong>
            </label>
            <select id="wac_filter_lists" name="wac_filter_lists[]" v-model="wacfilter.lists">
              <option
                v-for="(ListsType, index) in ListsTypes"
                :key="'ListsType-' + index"
                :value="ListsType.value"
              >{{ ListsType.label }}</option>
            </select>
          </div>
        </div>
        <div class="wac-col-3" v-if="checkItemsAvaiable(wacfilter.type)">
          <div class="wac-form">
            <label for="wac_filter_products">
              <strong>{{ getItemsLabel(wacfilter.type) }}</strong>
            </label>
            <customSelect
              v-on:selectOptions="selectOptions"
              :multiData="{
                options: [],
                searchable: true,
                placeholder: 'Enter 3 words',
                search_action: getItemsAction(wacfilter.type),
              }"
              :defaultOption="wacfilter.items"
              :multiName="index"
            ></customSelect>
          </div>
        </div>
        <div v-if="wacfilters.length > 1" @click="removeFilter(index)" class="wac-filter-close">
          <span class="dashicons dashicons-no-alt"></span>
        </div>
      </div>
      <div class="wac_buttons">
        <button type="button" @click="update" class="button-primary">Save</button>
        <button type="button" @click="cloneFilter" class="button-primary">Add Filter</button>
      </div>
    </div>
  </div>
</template>

<script>
import customSelect from "./helpers/customSelect";
export default {
  name: "wacfilter",
  props: ["nonce"],
  data() {
    return {
      loading: true,
      filterTypes: [],
      ListsTypes: [
        { label: "In List", value: "inList" },
        { label: "Not In List", value: "noList" },
      ],
      wacfilters: [
        {
          type: "all_products",
          lists: "inList",
          items: [],
        },
      ],
    };
  },
  created() {
    this.getFilters();
  },
  methods: {
    checkItemsAvaiable(filter_type) {
      let result = false;
      this.filterTypes.forEach(element => {
        if (element.value == filter_type) {
          result = element.has_item;
        }
      });
      return result;
    },
    getItemsLabel(filter_type) {
      let label;
      this.filterTypes.forEach(element => {
        if (element.value == filter_type) {
          label = element.items.label;
        }
      });
      return label;
    },
    getItemsAction(filter_type) {
      let action;
      this.filterTypes.forEach(element => {
        if (element.value == filter_type) {
          action = element.items.action;
        }
      });
      return action;
    },
    selectOptions(value) {
      this.wacfilters[value.name].items = value.selectOption;
    },
    cloneFilter() {
      this.wacfilters.push({
        type: "all_products",
        lists: "inList",
        items: [],
      });
    },
    removeFilter(index) {
      this.wacfilters.splice(index, 1);
    },
    update() {
      let formData = {
        action: "wac_save_filters",
        wacfilters: this.wacfilters,
        wac_nonce: this.nonce,
        post_id: wac_post.id,
      };
      axios
        .post(wac_helper_obj.ajax_url, Qs.stringify(formData))
        .then((response) => {
          this.$toasted.show(response.data.message, {
            position: "top-center",
            duration: 3000,
          });
        })
        .catch((error) => {
          console.log(error);
        });
    },
    getFilters() {
      this.loading = true;
      let formData = {
        action: "wac_get_filters",
        post_id: wac_post.id,
      };
      let root = this;
      axios
        .post(wac_helper_obj.ajax_url, Qs.stringify(formData))
        .then((response) => {
          if (response.data.post_meta != [] && response.data.post_meta != null) {
            root.wacfilters = response.data.post_meta;
          }
          response.data.filters_data.forEach(element => {
            root.filterTypes.push(element);
          });
          root.loading = false;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
  components: {
    customSelect,
  },
};
</script>
