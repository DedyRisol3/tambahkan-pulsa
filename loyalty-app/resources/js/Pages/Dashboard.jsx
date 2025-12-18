import { useEffect, useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import api from '../api';

// --- KOMPONEN LAYOUT (Inline) ---
const AuthenticatedLayout = ({ user, header, children, onLogout }) => (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <nav className="bg-white/10 backdrop-blur-md border-b border-white/10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex justify-between h-16">
                    <div className="flex">
                        <div className="shrink-0 flex items-center">
                            <Link href="/dashboard" className="font-bold text-xl text-white flex items-center gap-2">
                                <span className="text-2xl">💎</span>
                                Loyalty App
                            </Link>
                        </div>
                    </div>
                    <div className="flex items-center">
                        <div className="ml-3 relative flex items-center gap-4">
                            <span className="text-sm text-white/70">{user?.name}</span>
                            <button
                                onClick={onLogout}
                                className="text-sm text-red-400 hover:text-red-300 font-medium transition-colors"
                            >
                                Log Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        {header && (
            <header className="bg-white/5 backdrop-blur-sm">
                <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {header}
                </div>
            </header>
        )}

        <main>{children}</main>
    </div>
);

// --- KOMPONEN MODAL PULSA ---
const PulsaModal = ({ isOpen, onClose, balance, userId, onSuccess }) => {
    const [step, setStep] = useState(1);
    const [selectedPackage, setSelectedPackage] = useState(null);
    const [phoneNumber, setPhoneNumber] = useState('');
    const [provider, setProvider] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const [successData, setSuccessData] = useState(null);

    const packages = [
        { points: 100, pulsa: 5000 },
        { points: 200, pulsa: 10000 },
        { points: 400, pulsa: 25000 },
        { points: 750, pulsa: 50000 },
        { points: 1500, pulsa: 100000 },
    ];

    const providers = [
        { id: 'telkomsel', name: 'Telkomsel', color: 'from-red-500 to-red-600' },
        { id: 'xl', name: 'XL Axiata', color: 'from-blue-500 to-blue-600' },
        { id: 'indosat', name: 'Indosat', color: 'from-yellow-500 to-yellow-600' },
        { id: 'tri', name: 'Tri (3)', color: 'from-purple-500 to-purple-600' },
        { id: 'smartfren', name: 'Smartfren', color: 'from-pink-500 to-pink-600' },
        { id: 'axis', name: 'Axis', color: 'from-violet-500 to-violet-600' },
    ];

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(num);
    };

    const handleSubmit = async () => {
        if (!selectedPackage || !phoneNumber || !provider) {
            setError('Lengkapi semua data');
            return;
        }

        if (!/^08[0-9]{9,12}$/.test(phoneNumber)) {
            setError('Format nomor HP tidak valid (contoh: 081234567890)');
            return;
        }

        if (balance < selectedPackage.points) {
            setError('Saldo poin tidak mencukupi');
            return;
        }

        setIsLoading(true);
        setError('');

        try {
            const response = await api.post('/api/pulsa/redeem', {
                user_id: userId,
                phone_number: phoneNumber,
                provider: provider,
                points_used: selectedPackage.points
            });

            if (response.data.success) {
                setSuccessData(response.data.data);
                setStep(4);
                onSuccess();
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Terjadi kesalahan');
        } finally {
            setIsLoading(false);
        }
    };

    const resetModal = () => {
        setStep(1);
        setSelectedPackage(null);
        setPhoneNumber('');
        setProvider('');
        setError('');
        setSuccessData(null);
    };

    const handleClose = () => {
        resetModal();
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-white/10">
                {/* Header */}
                <div className="p-6 border-b border-white/10">
                    <div className="flex justify-between items-center">
                        <h2 className="text-xl font-bold text-white flex items-center gap-2">
                            <span className="text-2xl">📱</span>
                            Tukar Poin ke Pulsa
                        </h2>
                        <button onClick={handleClose} className="text-white/50 hover:text-white text-2xl">
                            ×
                        </button>
                    </div>
                    {/* Progress Steps */}
                    {step < 4 && (
                        <div className="flex items-center justify-center gap-2 mt-4">
                            {[1, 2, 3].map((s) => (
                                <div key={s} className="flex items-center">
                                    <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all ${
                                        step >= s 
                                            ? 'bg-gradient-to-r from-emerald-400 to-cyan-400 text-slate-900' 
                                            : 'bg-white/10 text-white/40'
                                    }`}>
                                        {s}
                                    </div>
                                    {s < 3 && (
                                        <div className={`w-12 h-0.5 mx-1 ${step > s ? 'bg-emerald-400' : 'bg-white/10'}`} />
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Content */}
                <div className="p-6">
                    {error && (
                        <div className="mb-4 p-3 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400 text-sm">
                            {error}
                        </div>
                    )}

                    {/* Step 1: Pilih Paket */}
                    {step === 1 && (
                        <div>
                            <p className="text-white/60 text-sm mb-4">Pilih nominal pulsa yang ingin ditukar:</p>
                            <div className="grid grid-cols-1 gap-3">
                                {packages.map((pkg) => (
                                    <button
                                        key={pkg.points}
                                        onClick={() => {
                                            setSelectedPackage(pkg);
                                            setStep(2);
                                        }}
                                        disabled={balance < pkg.points}
                                        className={`p-4 rounded-xl border transition-all flex justify-between items-center ${
                                            balance < pkg.points
                                                ? 'bg-white/5 border-white/5 opacity-50 cursor-not-allowed'
                                                : 'bg-white/5 border-white/10 hover:border-emerald-400/50 hover:bg-white/10'
                                        }`}
                                    >
                                        <div className="text-left">
                                            <p className="text-white font-semibold">{formatRupiah(pkg.pulsa)}</p>
                                            <p className="text-white/50 text-sm">{pkg.points} Poin</p>
                                        </div>
                                        {balance < pkg.points ? (
                                            <span className="text-xs text-red-400">Poin kurang</span>
                                        ) : (
                                            <span className="text-emerald-400 text-xl">→</span>
                                        )}
                                    </button>
                                ))}
                            </div>
                            <p className="text-white/40 text-xs mt-4 text-center">
                                Saldo Anda: <span className="text-emerald-400 font-semibold">{balance} Poin</span>
                            </p>
                        </div>
                    )}

                    {/* Step 2: Input Nomor & Provider */}
                    {step === 2 && (
                        <div>
                            <div className="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                                <p className="text-emerald-400 text-sm">Paket dipilih:</p>
                                <p className="text-white font-bold text-lg">
                                    {formatRupiah(selectedPackage.pulsa)} ({selectedPackage.points} Poin)
                                </p>
                            </div>

                            <div className="mb-4">
                                <label className="block text-white/70 text-sm mb-2">Nomor HP Tujuan</label>
                                <input
                                    type="tel"
                                    value={phoneNumber}
                                    onChange={(e) => setPhoneNumber(e.target.value.replace(/\D/g, ''))}
                                    placeholder="081234567890"
                                    className="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 focus:outline-none focus:border-emerald-400/50 transition-colors"
                                />
                            </div>

                            <div className="mb-6">
                                <label className="block text-white/70 text-sm mb-2">Provider</label>
                                <div className="grid grid-cols-2 gap-2">
                                    {providers.map((p) => (
                                        <button
                                            key={p.id}
                                            onClick={() => setProvider(p.id)}
                                            className={`p-3 rounded-xl border text-sm font-medium transition-all ${
                                                provider === p.id
                                                    ? `bg-gradient-to-r ${p.color} border-transparent text-white`
                                                    : 'bg-white/5 border-white/10 text-white/70 hover:bg-white/10'
                                            }`}
                                        >
                                            {p.name}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    onClick={() => setStep(1)}
                                    className="flex-1 py-3 rounded-xl border border-white/10 text-white/70 hover:bg-white/5 transition-colors"
                                >
                                    Kembali
                                </button>
                                <button
                                    onClick={() => {
                                        if (phoneNumber && provider) {
                                            setStep(3);
                                        } else {
                                            setError('Lengkapi nomor HP dan provider');
                                        }
                                    }}
                                    className="flex-1 py-3 rounded-xl bg-gradient-to-r from-emerald-400 to-cyan-400 text-slate-900 font-semibold hover:opacity-90 transition-opacity"
                                >
                                    Lanjut
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 3: Konfirmasi */}
                    {step === 3 && (
                        <div>
                            <div className="bg-white/5 rounded-xl p-4 mb-6 space-y-3">
                                <div className="flex justify-between">
                                    <span className="text-white/50">Nominal Pulsa</span>
                                    <span className="text-white font-semibold">{formatRupiah(selectedPackage.pulsa)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-white/50">Poin Digunakan</span>
                                    <span className="text-white font-semibold">{selectedPackage.points} Poin</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-white/50">Nomor Tujuan</span>
                                    <span className="text-white font-semibold">{phoneNumber}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-white/50">Provider</span>
                                    <span className="text-white font-semibold">
                                        {providers.find(p => p.id === provider)?.name}
                                    </span>
                                </div>
                                <hr className="border-white/10" />
                                <div className="flex justify-between">
                                    <span className="text-white/50">Sisa Poin</span>
                                    <span className="text-emerald-400 font-semibold">{balance - selectedPackage.points} Poin</span>
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <button
                                    onClick={() => setStep(2)}
                                    disabled={isLoading}
                                    className="flex-1 py-3 rounded-xl border border-white/10 text-white/70 hover:bg-white/5 transition-colors disabled:opacity-50"
                                >
                                    Kembali
                                </button>
                                <button
                                    onClick={handleSubmit}
                                    disabled={isLoading}
                                    className="flex-1 py-3 rounded-xl bg-gradient-to-r from-emerald-400 to-cyan-400 text-slate-900 font-semibold hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    {isLoading ? (
                                        <>
                                            <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                            </svg>
                                            Memproses...
                                        </>
                                    ) : (
                                        'Konfirmasi Tukar'
                                    )}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Step 4: Sukses */}
                    {step === 4 && successData && (
                        <div className="text-center">
                            <div className="w-20 h-20 bg-gradient-to-r from-emerald-400 to-cyan-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span className="text-4xl">✓</span>
                            </div>
                            <h3 className="text-xl font-bold text-white mb-2">Berhasil!</h3>
                            <p className="text-white/60 mb-6">
                                Permintaan penukaran pulsa Anda sedang diproses. Pulsa akan dikirim dalam 1x24 jam.
                            </p>
                            
                            <div className="bg-white/5 rounded-xl p-4 mb-6 text-left space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-white/50">Nomor Tujuan</span>
                                    <span className="text-white">{successData.phone_number}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-white/50">Provider</span>
                                    <span className="text-white">{successData.provider}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-white/50">Nominal</span>
                                    <span className="text-white">{formatRupiah(successData.pulsa_amount)}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-white/50">Status</span>
                                    <span className="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 rounded text-xs uppercase">
                                        {successData.status}
                                    </span>
                                </div>
                            </div>

                            <button
                                onClick={handleClose}
                                className="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-400 to-cyan-400 text-slate-900 font-semibold hover:opacity-90 transition-opacity"
                            >
                                Selesai
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

// --- KOMPONEN RIWAYAT PULSA ---
const PulsaHistoryCard = ({ history }) => {
    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(num);
    };

    const getStatusBadge = (status) => {
        const styles = {
            pending: 'bg-yellow-500/20 text-yellow-400',
            processing: 'bg-blue-500/20 text-blue-400',
            completed: 'bg-emerald-500/20 text-emerald-400',
            rejected: 'bg-red-500/20 text-red-400'
        };
        const labels = {
            pending: 'Menunggu',
            processing: 'Diproses',
            completed: 'Selesai',
            rejected: 'Ditolak'
        };
        return (
            <span className={`px-2 py-0.5 rounded text-xs uppercase font-medium ${styles[status] || styles.pending}`}>
                {labels[status] || status}
            </span>
        );
    };

    if (history.length === 0) {
        return (
            <div className="text-center py-8 text-white/40">
                <span className="text-4xl block mb-2">📱</span>
                Belum ada riwayat penukaran pulsa
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {history.map((item) => (
                <div key={item.id} className="bg-white/5 rounded-xl p-4 border border-white/5">
                    <div className="flex justify-between items-start mb-2">
                        <div>
                            <p className="text-white font-semibold">{formatRupiah(item.pulsa_amount)}</p>
                            <p className="text-white/50 text-sm">{item.phone_number} • {item.provider}</p>
                        </div>
                        {getStatusBadge(item.status)}
                    </div>
                    <div className="flex justify-between items-center text-xs text-white/40">
                        <span>{item.points_used} Poin</span>
                        <span>{new Date(item.created_at).toLocaleString('id-ID')}</span>
                    </div>
                    {item.admin_notes && item.status === 'rejected' && (
                        <p className="mt-2 text-xs text-red-400 bg-red-500/10 p-2 rounded">
                            {item.admin_notes}
                        </p>
                    )}
                </div>
            ))}
        </div>
    );
};

// --- KOMPONEN UTAMA DASHBOARD ---
export default function Dashboard() {
    const [balance, setBalance] = useState(0);
    const [history, setHistory] = useState([]);
    const [pulsaHistory, setPulsaHistory] = useState([]);
    const [activeTab, setActiveTab] = useState('points');
    const [isPulsaModalOpen, setIsPulsaModalOpen] = useState(false);

    const user = useMemo(() => {
        const storedUser = localStorage.getItem('user');
        return storedUser ? JSON.parse(storedUser) : null;
    }, []);

    const logout = () => {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        router.visit('/');
    };

    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        if (!token || !user) {
            router.visit('/');
        }
    }, [user]);

    const fetchData = async () => {
        if (!user) return;

        try {
            // 1. Ambil Saldo Poin
            const pointsRes = await api.get(
                `${import.meta.env.VITE_LOYALTY_SERVICE_URL}/api/points/user/${user.id}`
            );
            setBalance(pointsRes.data.total_points || 0);

            // 2. Ambil Riwayat Poin
            const historyRes = await api.get(
                `${import.meta.env.VITE_LOYALTY_SERVICE_URL}/api/history/${user.id}`
            );

            const rawTransactions = historyRes.data.transactions || [];
            const formattedData = rawTransactions.map(t => {
                const isCredit = t.type === 'earn' || parseFloat(t.amount) > 0;
                return {
                    id: t.id,
                    created_at: t.created_at,
                    order_id: t.description || 'Transaksi Poin',
                    type: isCredit ? 'credit' : 'debit',
                    points_amount: Math.abs(parseFloat(t.amount))
                };
            });
            formattedData.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            setHistory(formattedData);

            // 3. Ambil Riwayat Pulsa
            const pulsaRes = await api.get(`/api/pulsa/history/${user.id}`);
            if (pulsaRes.data.success) {
                setPulsaHistory(pulsaRes.data.data);
            }

        } catch (error) {
            console.error('Gagal mengambil data:', error);
        }
    };

    useEffect(() => {
        fetchData();
    }, [user]);

    if (!user) return null;

    return (
        <AuthenticatedLayout
            user={user}
            onLogout={logout}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Dashboard Loyalty</h2>}
        >
            <Head title="Dashboard Loyalty" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    {/* Kartu Saldo & Tukar Pulsa */}
                    <div className="grid md:grid-cols-2 gap-6 mb-6">
                        {/* Saldo */}
                        <div className="bg-white/10 backdrop-blur-md overflow-hidden rounded-2xl border border-white/10">
                            <div className="p-6">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-12 h-12 bg-gradient-to-r from-emerald-400 to-cyan-400 rounded-xl flex items-center justify-center">
                                        <span className="text-2xl">💎</span>
                                    </div>
                                    <div>
                                        <h3 className="text-white/60 text-sm">Saldo Poin Anda</h3>
                                        <p className="text-3xl font-bold text-white">{balance.toLocaleString('id-ID')}</p>
                                    </div>
                                </div>
                                <p className="text-white/40 text-sm">
                                    Tukarkan poin Anda dengan berbagai hadiah menarik!
                                </p>
                            </div>
                        </div>

                        {/* Tukar Pulsa CTA */}
                        <div className="bg-gradient-to-r from-emerald-500/20 to-cyan-500/20 backdrop-blur-md overflow-hidden rounded-2xl border border-emerald-500/30">
                            <div className="p-6">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-12 h-12 bg-gradient-to-r from-emerald-400 to-cyan-400 rounded-xl flex items-center justify-center">
                                        <span className="text-2xl">📱</span>
                                    </div>
                                    <div>
                                        <h3 className="text-white font-semibold">Tukar Poin ke Pulsa</h3>
                                        <p className="text-white/60 text-sm">Mulai dari 100 poin</p>
                                    </div>
                                </div>
                                <button
                                    onClick={() => setIsPulsaModalOpen(true)}
                                    className="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-400 to-cyan-400 text-slate-900 font-semibold hover:opacity-90 transition-opacity"
                                >
                                    Tukar Sekarang
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Tabs */}
                    <div className="bg-white/10 backdrop-blur-md overflow-hidden rounded-2xl border border-white/10">
                        <div className="border-b border-white/10">
                            <div className="flex">
                                <button
                                    onClick={() => setActiveTab('points')}
                                    className={`flex-1 py-4 text-center font-medium transition-colors ${
                                        activeTab === 'points'
                                            ? 'text-emerald-400 border-b-2 border-emerald-400'
                                            : 'text-white/50 hover:text-white/70'
                                    }`}
                                >
                                    Riwayat Poin
                                </button>
                                <button
                                    onClick={() => setActiveTab('pulsa')}
                                    className={`flex-1 py-4 text-center font-medium transition-colors ${
                                        activeTab === 'pulsa'
                                            ? 'text-emerald-400 border-b-2 border-emerald-400'
                                            : 'text-white/50 hover:text-white/70'
                                    }`}
                                >
                                    Riwayat Pulsa
                                </button>
                            </div>
                        </div>

                        <div className="p-6">
                            {activeTab === 'points' ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-white/10">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Tanggal</th>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Keterangan</th>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Tipe</th>
                                                <th className="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-white/5">
                                            {history.length > 0 ? (
                                                history.map((item) => (
                                                    <tr key={item.id} className="hover:bg-white/5 transition-colors">
                                                        <td className="px-4 py-4 whitespace-nowrap text-sm text-white/60">
                                                            {new Date(item.created_at).toLocaleString('id-ID')}
                                                        </td>
                                                        <td className="px-4 py-4 whitespace-nowrap text-sm text-white">
                                                            {item.order_id}
                                                        </td>
                                                        <td className="px-4 py-4 whitespace-nowrap text-sm">
                                                            <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                                                item.type === 'credit' 
                                                                    ? 'bg-emerald-500/20 text-emerald-400' 
                                                                    : 'bg-red-500/20 text-red-400'
                                                            }`}>
                                                                {item.type === 'credit' ? 'MASUK' : 'KELUAR'}
                                                            </span>
                                                        </td>
                                                        <td className={`px-4 py-4 whitespace-nowrap text-sm font-bold ${
                                                            item.type === 'credit' ? 'text-emerald-400' : 'text-red-400'
                                                        }`}>
                                                            {item.type === 'credit' ? '+' : '-'}{item.points_amount}
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan="4" className="px-4 py-8 text-center text-sm text-white/40">
                                                        Belum ada riwayat transaksi poin.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <PulsaHistoryCard history={pulsaHistory} />
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal Penukaran Pulsa */}
            <PulsaModal
                isOpen={isPulsaModalOpen}
                onClose={() => setIsPulsaModalOpen(false)}
                balance={balance}
                userId={user?.id}
                onSuccess={fetchData}
            />
        </AuthenticatedLayout>
    );
}
