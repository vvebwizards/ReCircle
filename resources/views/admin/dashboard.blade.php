@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('admin-content')
  <div class="admin-topbar">
    <div>
      <h1>Admin Dashboard</h1>
      <div class="tb-sub">System status and recent activity</div>
    </div>
   
  </div>

  <section class="admin-stats">
    <div class="a-card stat" style="--accent:#16a34a">
      <div class="a-icon"><i class="fa-solid fa-cloud"></i></div>
      <div>
  <div class="a-value" id="a-co2" data-value="{{ number_format($co2Saved, 0, '.', '') }}">{{ number_format($co2Saved, 1) }}</div>
        <div class="a-label">Kg CO₂ Saved</div>
      </div>
    </div>
    <div class="a-card stat" style="--accent:#2563eb">
      <div class="a-icon"><i class="fa-regular fa-user"></i></div>
      <div>
  <div class="a-value" id="a-users" data-value="{{ $usersCount }}">{{ $usersCount }}</div>
        <div class="a-label">Users</div>
      </div>
    </div>
    <div class="a-card stat" style="--accent:#f59e0b">
      <div class="a-icon"><i class="fa-solid fa-list"></i></div>
      <div>
  <div class="a-value" id="a-listings" data-value="{{ $listingsCount }}">{{ $listingsCount }}</div>
        <div class="a-label">Listings</div>
      </div>
    </div>
    <div class="a-card stat" style="--accent:#dc2626">
      <div class="a-icon"><i class="fa-solid fa-flag"></i></div>
      <div>
  <div class="a-value" id="a-flags" data-value="{{ $pendingReclamations }}">{{ $pendingReclamations }}</div>
        <div class="a-label">Flags</div>
      </div>
    </div>
  </section>

  <section class="admin-grid">
    <div class="a-card wide">
      <div class="a-title"><i class="fa-solid fa-users"></i> Recent Users</div>
      <table class="a-table">
        <thead><tr><th>Name</th><th>Role</th><th>Joined</th><th>Status</th></tr></thead>
        <tbody id="a-users-body">
          @foreach($recentUsers as $u)
            <tr>
              <td>{{ $u->name }}</td>
              <td>{{ ucfirst(strtolower($u->role->value ?? 'user')) }}</td>
              <td>{{ $u->created_at?->diffForHumans() }}</td>
              <td>{{ $u->blocked_at ? 'Blocked' : 'Active' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  <!-- Modal des notifications -->
  <div id="notificationModal" class="notification-modal" style="display: none;">
    <div class="notification-modal-content">
      <div class="notification-header">
        <h3><i class="fa-solid fa-bell"></i> Notifications</h3>
        <button id="closeNotificationModal" class="close-btn">&times;</button>
      </div>
      <div class="notification-body" id="notificationBody">
        <!-- Les notifications seront chargées ici -->
      </div>
      <div class="notification-footer">
        <button id="markAllRead" class="btn btn-secondary">Marquer tout comme lu</button>
      </div>
    </div>
  </div>

  <script>
    // Variables globales pour les notifications
    let notificationCheckInterval;
    let unreadCount = 0;

    // Fonction pour charger les notifications non lues (pour le badge)
    async function loadUnreadNotifications() {
      try {
        // Vérifier si l'utilisateur est connecté et admin
        const isAdmin = @json(auth()->check() && auth()->user()->role->value === 'admin');
        
        if (!isAdmin) {
          console.log('Utilisateur non admin, notifications désactivées');
          return [];
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
          console.error('Token CSRF non trouvé');
          return [];
        }

        const response = await fetch('/notifications/unread', {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin' // Inclure les cookies de session
        });

        if (!response.ok) {
          if (response.status === 401) {
            console.log('Utilisateur non authentifié, notifications désactivées');
            return [];
          }
          console.error('Erreur HTTP:', response.status);
          return [];
        }

        const data = await response.json();
        
        if (data.success) {
          updateNotificationBadge(data.count);
          return data.notifications;
        } else {
          console.error('Erreur de notification:', data.message);
          return [];
        }
      } catch (error) {
        console.error('Erreur lors du chargement des notifications:', error);
      }
      return [];
    }

    // Fonction pour charger TOUTES les notifications (lues et non lues)
    async function loadAllNotifications() {
      try {
        const isAdmin = @json(auth()->check() && auth()->user()->role->value === 'admin');
        
        if (!isAdmin) {
          console.log('Utilisateur non admin, notifications désactivées');
          return [];
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
          console.error('Token CSRF non trouvé');
          return [];
        }

        const response = await fetch('/notifications/all', {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin'
        });

        if (!response.ok) {
          if (response.status === 401) {
            console.log('Utilisateur non authentifié, notifications désactivées');
            return [];
          }
          console.error('Erreur HTTP:', response.status);
          return [];
        }

        const data = await response.json();
        
        if (data.success) {
          return data.notifications;
        } else {
          console.error('Erreur de notification:', data.message);
          return [];
        }
      } catch (error) {
        console.error('Erreur lors du chargement des notifications:', error);
      }
      return [];
    }

    // Fonction pour mettre à jour le badge de notification
    function updateNotificationBadge(count) {
      const badge = document.getElementById('notificationBadge');
      const countElement = document.getElementById('notificationCount');
      
      if (!badge || !countElement) {
        console.error('Éléments de notification non trouvés');
        return;
      }
      
      if (count > 0) {
        badge.style.display = 'flex';
        countElement.textContent = count;
        unreadCount = count;
      } else {
        badge.style.display = 'none';
        unreadCount = 0;
      }
    }

    // Fonction pour afficher les notifications dans le modal
    function displayNotifications(notifications) {
      const body = document.getElementById('notificationBody');
      
      if (notifications.length === 0) {
        body.innerHTML = '<div class="no-notifications"><i class="fa-solid fa-bell-slash"></i><p>Aucune notification</p></div>';
        return;
      }

      body.innerHTML = notifications.map(notification => {
        const notificationData = typeof notification.data === 'string' ? JSON.parse(notification.data) : notification.data;
        const isRead = notification.read_at !== null;
        
        return `
        <div class="notification-item ${!isRead ? 'unread' : 'read'}" data-id="${notification.id}">
          <div class="notification-icon-item">
            <i class="fa-solid fa-truck"></i>
          </div>
          <div class="notification-content">
            <h4>${notificationData.title}</h4>
            <p>${notificationData.message}</p>
            <small>${new Date(notification.created_at).toLocaleString('fr-FR')} ${isRead ? '(Lue)' : '(Non lue)'}</small>
          </div>
          <div class="notification-actions">
            ${!isRead ? `<button class="btn-mark-read" data-id="${notification.id}" title="Marquer comme lu">
              <i class="fa-solid fa-check"></i>
            </button>` : ''}
            <button class="btn-delete-notification" data-id="${notification.id}" title="Supprimer">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      `;
      }).join('');
    }

    // Fonction pour marquer une notification comme lue
    async function markNotificationAsRead(notificationId) {
      try {
        const response = await fetch('/notifications/mark-read', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            notification_id: notificationId
          })
        });

        const data = await response.json();
        
        if (data.success) {
          // Recharger les notifications
          const notifications = await loadAllNotifications();
          displayNotifications(notifications);
          // Mettre à jour le badge avec les non lues
          await loadUnreadNotifications();
        }
      } catch (error) {
        console.error('Erreur lors de la mise à jour:', error);
      }
    }

    // Fonction pour supprimer une notification
    async function deleteNotification(notificationId) {
      try {
        const response = await fetch('/notifications/delete', {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            notification_id: notificationId
          })
        });

        const data = await response.json();
        
        if (data.success) {
          // Recharger les notifications
          const notifications = await loadAllNotifications();
          displayNotifications(notifications);
          // Mettre à jour le badge avec les non lues
          await loadUnreadNotifications();
        }
      } catch (error) {
        console.error('Erreur lors de la suppression:', error);
      }
    }

    // Fonction pour marquer toutes les notifications comme lues
    async function markAllNotificationsAsRead() {
      try {
        const response = await fetch('/notifications/mark-all-read', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin'
        });

        const data = await response.json();
        
        if (data.success) {
          // Recharger les notifications
          const notifications = await loadAllNotifications();
          displayNotifications(notifications);
          // Mettre à jour le badge avec les non lues
          await loadUnreadNotifications();
        }
      } catch (error) {
        console.error('Erreur lors de la mise à jour:', error);
      }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
      // Vérifier si l'utilisateur est admin avant d'initialiser les notifications
      const isAdmin = @json(auth()->check() && auth()->user()->role->value === 'admin');
      
      if (!isAdmin) {
        console.log('Notifications désactivées pour utilisateur non admin');
        return;
      }

      // Charger les notifications au chargement de la page
      loadUnreadNotifications();

      // Vérifier les nouvelles notifications toutes les 30 secondes
      notificationCheckInterval = setInterval(loadUnreadNotifications, 30000);

      // Ouvrir le modal des notifications quand on clique sur le badge
      const notificationBadge = document.getElementById('notificationBadge');
      if (notificationBadge) {
        notificationBadge.addEventListener('click', async function() {
          const modal = document.getElementById('notificationModal');
          const notifications = await loadAllNotifications(); // Charger TOUTES les notifications
          displayNotifications(notifications);
          if (modal) modal.style.display = 'block';
        });
      }

      // Fermer le modal
      const closeButton = document.getElementById('closeNotificationModal');
      if (closeButton) {
        closeButton.addEventListener('click', function() {
          const modal = document.getElementById('notificationModal');
          if (modal) modal.style.display = 'none';
        });
      }

      // Fermer le modal en cliquant à l'extérieur
      window.addEventListener('click', function(event) {
        const modal = document.getElementById('notificationModal');
        if (event.target === modal && modal) {
          modal.style.display = 'none';
        }
      });

      // Marquer toutes les notifications comme lues
      const markAllButton = document.getElementById('markAllRead');
      if (markAllButton) {
        markAllButton.addEventListener('click', markAllNotificationsAsRead);
      }

      // Event delegation pour les boutons d'action des notifications
      document.addEventListener('click', function(event) {
        if (event.target.closest('.btn-mark-read')) {
          const notificationId = event.target.closest('.btn-mark-read').dataset.id;
          markNotificationAsRead(notificationId);
        }

        if (event.target.closest('.btn-delete-notification')) {
          const notificationId = event.target.closest('.btn-delete-notification').dataset.id;
          deleteNotification(notificationId);
        }
      });
    });

    // Nettoyer l'intervalle quand la page se ferme
    window.addEventListener('beforeunload', function() {
      if (notificationCheckInterval) {
        clearInterval(notificationCheckInterval);
      }
    });
  </script>

  <style>
    /* Styles pour le modal des notifications */
    .notification-modal {
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .notification-modal-content {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 500px;
      max-height: 80vh;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .notification-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      border-bottom: 1px solid #e5e7eb;
      background: #f8fafc;
    }

    .notification-header h3 {
      margin: 0;
      color: #1f2937;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #6b7280;
    }

    .notification-body {
      max-height: 400px;
      overflow-y: auto;
      padding: 0;
    }

    .notification-item {
      display: flex;
      align-items: center;
      padding: 16px 20px;
      border-bottom: 1px solid #f3f4f6;
      transition: background-color 0.2s;
    }

    .notification-item:hover {
      background-color: #f8fafc;
    }

    .notification-item.unread {
      background-color: #fef3f2;
      border-left: 4px solid #ef4444;
    }

    .notification-icon-item {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      margin-right: 12px;
    }

    .notification-content {
      flex: 1;
    }

    .notification-content h4 {
      margin: 0 0 4px 0;
      font-size: 14px;
      font-weight: 600;
      color: #1f2937;
    }

    .notification-content p {
      margin: 0 0 4px 0;
      font-size: 13px;
      color: #6b7280;
    }

    .notification-content small {
      color: #9ca3af;
      font-size: 11px;
    }

    .notification-actions {
      display: flex;
      gap: 8px;
    }

    .btn-mark-read, .btn-delete-notification {
      background: none;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 6px 8px;
      cursor: pointer;
      color: #6b7280;
      transition: all 0.2s;
    }

    .btn-mark-read:hover {
      background: #10b981;
      color: white;
      border-color: #10b981;
    }

    .btn-delete-notification:hover {
      background: #ef4444;
      color: white;
      border-color: #ef4444;
    }

    .notification-footer {
      padding: 16px 20px;
      border-top: 1px solid #e5e7eb;
      background: #f8fafc;
    }

    .no-notifications {
      text-align: center;
      padding: 40px 20px;
      color: #6b7280;
    }

    .no-notifications i {
      font-size: 48px;
      margin-bottom: 16px;
      opacity: 0.5;
    }

    .btn {
      padding: 8px 16px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn-secondary {
      background: #6b7280;
      color: white;
    }

    .btn-secondary:hover {
      background: #4b5563;
    }
  </style>
@endsection
