import { useQuery } from '@tanstack/react-query'
import { getNotification, listNotifications, notificationKeys } from '@/api/notificationApi'

export function useNotifications() {
  return useQuery({
    queryKey: notificationKeys.list,
    queryFn: listNotifications,
  })
}

export function useNotification(id: string | null) {
  return useQuery({
    queryKey: notificationKeys.detail(id ?? ''),
    queryFn: () => getNotification(id!),
    enabled: Boolean(id),
  })
}
