import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  getNotification,
  listNotifications,
  notificationKeys,
  replayNotification,
  type NotificationListFilters,
} from '@/api/notificationApi'
import { useSession } from './useSession'

export function useNotifications(filters: NotificationListFilters = {}) {
  const session = useSession()

  return useQuery({
    queryKey: notificationKeys.list(filters),
    queryFn: () => listNotifications(filters),
    enabled: session.data === true,
    placeholderData: (previous) => previous,
  })
}

export function useNotification(id: string | null) {
  return useQuery({
    queryKey: notificationKeys.detail(id ?? ''),
    queryFn: () => getNotification(id!),
    enabled: Boolean(id),
  })
}

export function useReplayNotification() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: replayNotification,
    onSuccess: (_data, id) => {
      void queryClient.invalidateQueries({ queryKey: notificationKeys.all })
      void queryClient.invalidateQueries({ queryKey: notificationKeys.detail(id) })
    },
  })
}
