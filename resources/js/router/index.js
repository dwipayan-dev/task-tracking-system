import { createRouter, createWebHistory } from 'vue-router'
import { URL } from "../helper"
import axios from 'axios';

const router = createRouter({
  history: createWebHistory(import.meta.env.VITE_BASE_URL || '/'),
  routes: [
    {
      path: '/admin/',
      component: () => import('../layouts/default.vue'),
      children: [
        {
          path: 'dashboard',
          component: () => import('../pages/dashboard.vue'),
          meta: {
            requiresPermission: 'dashboard-read',
          },
        },
        {
          path: 'employee',
          component: () => import('../pages/users.vue'),
          meta: {
            requiresPermission: 'employee-read',
          },
        },
        {
          path: 'role-permission',
          component: () => import('../pages/role-permission.vue'),
          meta: {
            requiresPermission: 'role-read',
          },
        },
        {
          path: 'edit-role-permission/:id',
          component: () => import('../pages/edit-role-permission.vue'),
          meta: {
            requiresPermission: 'role-update',
          },
        },
        {
          path: 'manage-task',
          component: () => import('../pages/manage-task.vue'),
          meta: {
            requiresPermission: 'manage_task-read',
          },
        },
        {
          path: 'audit-log',
          component: () => import('../pages/audit-log.vue'),
          meta: {
            requiresPermission: 'audit_log-read',
          },
        },
        {
          path: 'all-task',
          component: () => import('../pages/all-task.vue'),
          meta: {
            requiresPermission: 'all_task-read',
          },
        },
        {
          path: 'video-chat',
          component: () => import('../pages/video-chat.vue'),
        },
      ],
    },
    {
      path: '/',
      component: () => import('../layouts/blank.vue'),
      children: [
        {
          path: '/',
          component: () => import('../pages/login.vue'),
          beforeEnter: (to, from, next) => {
            if (isAuthenticated()) {
              next({ path: '/admin/dashboard' });
            } else {
              next();
            }
          },
        },
        {
          path: '/forbidden',
          component: () => import('../pages/forbidden.vue'),
        },
        {
          path: '/:pathMatch(.*)*',
          component: () => import('../pages/[...all].vue'),
        },
      ],
    },
  ],
})

router.beforeEach(async (to, from, next) => {
  if (to.path !== '/' && !isAuthenticated()) {
    return next({ path: '/' })
  }
  const requiresPermission = to.meta.requiresPermission;
  if (requiresPermission) {
    const token = localStorage.getItem('APP_TOKEN')
    const headers = {
      headers: {
        Authorization: 'Bearer ' + token,
        Accept: 'application/json',
      }
    }
    await axios.get(URL + "/api/user-details", headers)
      .then(async response => {
        if (!response.data.result.roles[0]['permissions'].some(item => item['name'] === requiresPermission)) {
          return next('/forbidden');
        } else {
          return next();
        }
      }).catch(async error => {
        return next();
      });
  } else {
    return next();
  }
})

function isAuthenticated() {
  return Boolean(localStorage.getItem('APP_TOKEN'))
}
export default router
