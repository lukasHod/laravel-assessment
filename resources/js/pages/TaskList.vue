<template>
  <div>
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Tasks</h1>
        <p class="mt-1 text-sm text-gray-500">Manage and track all your tasks.</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <router-link
          to="/tasks/create"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
        >
          + New Task
        </router-link>
      </div>
    </div>

    <!-- Filter bar -->
    <div class="mt-6 bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <input
          v-model="search"
          type="text"
          placeholder="Search tasks by name…"
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
        />
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-medium text-gray-500 mr-1">Status:</span>

        <button
          type="button"
          @click="clearStatusFilter"
          class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
          :class="selectedStatuses.length === 0
            ? 'bg-primary-50 text-primary-700 border-primary-300'
            : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'"
        >
          All
        </button>

        <button
          v-for="opt in statusOptions"
          :key="opt.value"
          type="button"
          @click="toggleStatus(opt.value)"
          class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
          :class="selectedStatuses.includes(opt.value)
            ? opt.activeClass
            : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="mt-10 flex justify-center">
      <div class="text-gray-400 text-sm">Loading tasks…</div>
    </div>

    <!-- Empty state -->
    <div v-else-if="tasks.length === 0" class="mt-6 text-center py-16 bg-white rounded-xl border-2 border-dashed border-gray-200">
      <p class="text-gray-500 font-medium">No tasks found.</p>
      <p class="text-gray-400 text-sm mt-1">
        {{ hasActiveFilters ? 'Try adjusting your search or filters.' : 'Get started by creating your first task.' }}
      </p>
      <router-link
        v-if="!hasActiveFilters"
        to="/tasks/create"
        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition-colors"
      >
        Create task
      </router-link>
    </div>

    <!-- Task table -->
    <div v-else class="mt-6 overflow-hidden shadow-sm ring-1 ring-black/5 rounded-xl">
      <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead>
          <tr class="bg-gray-50">
            <th class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Task</th>
            <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
            <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
            <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="task in tasks"
            :key="task.id"
            class="hover:bg-gray-50 transition-colors cursor-pointer"
            @click="viewTask(task.id)"
          >
            <td class="py-4 pl-6 pr-3">
              <div class="font-medium text-gray-900 text-sm">{{ task.name }}</div>
              <div v-if="task.description" class="text-gray-400 text-xs mt-0.5 max-w-xs truncate">
                {{ task.description }}
              </div>
            </td>
            <td class="px-3 py-4">
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusClass(task.status)">
                {{ statusLabel(task.status) }}
              </span>
            </td>
            <td class="px-3 py-4">
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="priorityClass(task.priority)">
                {{ task.priority }}
              </span>
            </td>
            <td class="px-3 py-4 text-sm text-gray-500">
              <span v-if="task.due_date" :class="isOverdue(task) ? 'text-red-600 font-medium' : ''">
                {{ formatDate(task.due_date) }}
              </span>
              <span v-else class="text-gray-300">—</span>
            </td>
            <td class="py-4 pl-3 pr-6 text-right" @click.stop>
              <div class="flex justify-end space-x-3">
                <router-link
                  :to="`/tasks/${task.id}/edit`"
                  class="text-sm text-primary-600 hover:text-primary-800 font-medium"
                >
                  Edit
                </router-link>
                <button
                  @click="deleteTask(task.id)"
                  class="text-sm text-red-500 hover:text-red-700 font-medium"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../composables/useApi.js';

const router = useRouter();
const tasks = ref([]);
const loading = ref(true);
const search = ref('');
const selectedStatuses = ref([]);

const statusOptions = [
  { value: 'todo',        label: 'To Do',      activeClass: 'bg-gray-100 text-gray-700 border-gray-300' },
  { value: 'in_progress', label: 'In Progress', activeClass: 'bg-yellow-100 text-yellow-800 border-yellow-300' },
  { value: 'done',        label: 'Done',        activeClass: 'bg-green-100 text-green-800 border-green-300' },
];

const hasActiveFilters = computed(
  () => search.value.trim() !== '' || selectedStatuses.value.length > 0,
);

const fetchTasks = async () => {
  loading.value = true;
  try {
    const params = {};
    if (search.value.trim()) {
      params.search = search.value.trim();
    }
    if (selectedStatuses.value.length > 0) {
      params.status = selectedStatuses.value;
    }
    const response = await api.get('/tasks', { params });
    tasks.value = response.data;
  } catch (err) {
    console.error('Failed to fetch tasks:', err);
  } finally {
    loading.value = false;
  }
};

const toggleStatus = (value) => {
  const index = selectedStatuses.value.indexOf(value);
  if (index === -1) {
    selectedStatuses.value = [...selectedStatuses.value, value];
  } else {
    selectedStatuses.value = selectedStatuses.value.filter((s) => s !== value);
  }
};

const clearStatusFilter = () => {
  selectedStatuses.value = [];
};

let debounceTimer;
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchTasks, 300);
});

watch(selectedStatuses, fetchTasks);

const deleteTask = async (id) => {
  if (!confirm('Delete this task? This cannot be undone.')) return;
  try {
    await api.delete(`/tasks/${id}`);
    tasks.value = tasks.value.filter((t) => t.id !== id);
  } catch (err) {
    console.error('Failed to delete task:', err);
  }
};

const viewTask = (id) => {
  router.push({ name: 'TaskShow', params: { id } });
};

const statusClass = (status) => {
  const map = {
    todo: 'bg-gray-100 text-gray-700',
    in_progress: 'bg-yellow-100 text-yellow-800',
    done: 'bg-green-100 text-green-800',
  };
  return map[status] ?? 'bg-gray-100 text-gray-700';
};

const statusLabel = (status) => {
  const map = { todo: 'To Do', in_progress: 'In Progress', done: 'Done' };
  return map[status] ?? status;
};

const priorityClass = (priority) => {
  const map = {
    low: 'bg-blue-100 text-blue-800',
    medium: 'bg-orange-100 text-orange-800',
    high: 'bg-red-100 text-red-800',
  };
  return map[priority] ?? 'bg-gray-100 text-gray-700';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const isOverdue = (task) => {
  if (!task.due_date || task.status === 'done') return false;
  return new Date(task.due_date) < new Date();
};

onMounted(fetchTasks);
</script>
