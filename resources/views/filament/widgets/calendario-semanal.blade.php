<x-filament-widgets::widget>
    <x-filament::section heading="Agenda da semana">
        @assets
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/pt-br.global.min.js"></script>
        @endassets

        <div class="mb-4 max-w-xs">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="profissionalId">
                    <option value="">Todos os profissionais</option>
                    @foreach ($this->getProfissionais() as $id => $nome)
                        <option value="{{ $id }}">{{ $nome }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <div
            wire:ignore
            x-data="{
                calendar: null,
                init() {
                    const wire = this.$wire;

                    this.calendar = new FullCalendar.Calendar(this.$refs.calendario, {
                        initialView: 'timeGridWeek',
                        locale: 'pt-br',
                        firstDay: 0,
                        slotMinTime: '07:00:00',
                        slotMaxTime: '21:00:00',
                        allDaySlot: false,
                        nowIndicator: true,
                        height: 'auto',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'timeGridWeek,timeGridDay,dayGridMonth',
                        },
                        events: (info, success, failure) => {
                            wire.buscarEventos(info.startStr, info.endStr).then(success).catch(failure);
                        },
                        eventDidMount: (info) => {
                            const prof = info.event.extendedProps.profissional;
                            info.el.title = info.event.title + (prof ? ' — ' + prof : '');
                        },
                    });

                    this.calendar.render();
                    wire.$watch('profissionalId', () => this.calendar.refetchEvents());
                },
            }"
        >
            <div x-ref="calendario"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>