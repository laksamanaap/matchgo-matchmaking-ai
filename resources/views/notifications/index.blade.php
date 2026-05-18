@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
        <section class="mx-auto max-w-5xl">
            <div class="mb-6 rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Notifikasi</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-4xl font-black text-[#0B5D1E]">Aktivitas MatchGo</h1>
                        <p class="mt-2 text-[#4B8B43]">Update saat tantangan diterima, AutoMatching ditemukan, atau pertandingan berubah status.</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] px-5 py-3 text-center">
                        <p class="text-xs font-bold text-[#4B8B43]">Belum dibaca</p>
                        <p class="text-3xl font-black text-[#0B5D1E]">{{ $unread_count }}</p>
                    </div>
                </div>
            </div>

            @if($notifications->count() > 0)
                <div class="grid gap-3">
                    @foreach($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $matchId = $data['reference_id'] ?? null;
                            $isUnread = is_null($notification->read_at);
                            $matchNotificationTypes = [
                                'challenge_created',
                                'challenge_accepted',
                                'auto_match_found',
                                'auto_match_confirmed',
                                'auto_match_cancelled',
                                'match_cancelled',
                            ];
                            $canOpenMatch = $matchId && in_array($data['type'] ?? '', $matchNotificationTypes, true);
                        @endphp

                        <div class="rounded-3xl border {{ $isUnread ? 'border-[#A5D6A7] bg-white' : 'border-[#DDEED8] bg-[#F8FCF4]' }} p-5 shadow-sm">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        @if($isUnread)
                                            <span class="rounded-full bg-[#2E8B3C] px-3 py-1 text-xs font-black text-white">Baru</span>
                                        @endif
                                        <span class="rounded-full bg-[#F1F8E9] px-3 py-1 text-xs font-bold text-[#2E7D32]">
                                            {{ str_replace('_', ' ', $data['type'] ?? 'notifikasi') }}
                                        </span>
                                    </div>
                                    <p class="text-lg font-black text-[#0B5D1E]">{{ $data['message'] ?? 'Ada notifikasi baru.' }}</p>
                                    <p class="mt-1 text-sm text-[#4B8B43]">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>

                                <div class="flex gap-2">
                                    @if($canOpenMatch)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button class="rounded-2xl bg-[#2E8B3C] px-4 py-2 text-sm font-black text-white transition hover:bg-[#23742F]">
                                                Lihat Match
                                            </button>
                                        </form>
                                    @elseif($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button class="rounded-2xl bg-[#F1F8E9] px-4 py-2 text-sm font-black text-[#2E7D32]">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-10 text-center text-[#4B8B43] shadow-xl shadow-[#1B5E20]/10">
                    Belum ada notifikasi.
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
