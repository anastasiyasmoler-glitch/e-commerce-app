import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

function UserAnalystForm({ user }) {
    const isAdmin = user.roles.includes('admin');
    const { data, setData, patch, processing, errors } = useForm({
        analyst: user.is_analyst,
    });

    const submit = (e) => {
        e.preventDefault();

        patch(route('admin.users.analyst', user.id), {
            preserveScroll: true,
        });
    };

    if (isAdmin) {
        return <span className="text-sm text-gray-500">-</span>;
    }

    return (
        <form onSubmit={submit} className="flex flex-wrap items-center gap-2">
            <label className="flex items-center">
                <Checkbox
                    name="analyst"
                    checked={data.analyst}
                    onChange={(e) => setData('analyst', e.target.checked)}
                />
                <span className="ms-2 text-sm text-gray-600">Analyst</span>
            </label>
            <PrimaryButton disabled={processing}>Save</PrimaryButton>
            <InputError message={errors.analyst} />
        </form>
    );
}

export default function Users({ users }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Users
                </h2>
            }
        >
            <Head title="Users" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="overflow-x-auto p-6 text-gray-900">
                            <table className="min-w-full text-left text-sm">
                                <thead>
                                    <tr className="border-b border-gray-200">
                                        <th className="py-2 pr-4 font-medium">
                                            Name
                                        </th>
                                        <th className="py-2 pr-4 font-medium">
                                            Email
                                        </th>
                                        <th className="py-2 pr-4 font-medium">
                                            Phone
                                        </th>
                                        <th className="py-2 pr-4 font-medium">
                                            Roles
                                        </th>
                                        <th className="py-2 font-medium">
                                            Analyst
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {users.map((user) => (
                                        <tr
                                            key={user.id}
                                            className="border-b border-gray-100"
                                        >
                                            <td className="py-2 pr-4">
                                                {user.name}
                                            </td>
                                            <td className="py-2 pr-4">
                                                {user.email}
                                            </td>
                                            <td className="py-2 pr-4">
                                                {user.phone ?? '-'}
                                            </td>
                                            <td className="py-2 pr-4">
                                                {user.roles.join(', ')}
                                            </td>
                                            <td className="py-2">
                                                <UserAnalystForm user={user} />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
