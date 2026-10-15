import { useQuery } from '@tanstack/react-query'
import { getNotification, listNotifications, notificationKeys } from '@/api/notificationApi'
import { useSession } from './useSession'

export function useNotifications() {
  const session = useSession()

  return useQuery({
    queryKey: notificationKeys.list,
    queryFn: listNotifications,
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
