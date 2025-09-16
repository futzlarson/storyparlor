@extends('layouts.app')

@section('content')

    {{-- Checkin. --}}
    @if ($rows)
        <div x-data="load">
            <div class="row p-4 pb-0 bg-white sticky-top justify-content-between border-bottom border-secondary-subtle">
                <div class="col">
                    <h2 class="mb-3">
                        {{ $event }}
                    </h2>

                    <div class="px-3 border rounded d-inline-block fs-4 me-3">
                        <span class="fw-bold" x-html="available"></span>
                        tickets available x
                        <span class="fw-bold text-success">${{ $cost }}</span>
                    </div>

                    <span @click="sold++" :class="soldOut && 'disabled'" class="btn btn-success d-inline fw-bold me-3">Sell ticket</span>
                    <span @click="sold--" class="btn btn-danger d-inline fw-bold">No show</span>
                </div>

                <ul class="col-4 mb-4 list-group list-group-horizontal text-center justify-content-end">
                    <li class="list-group-item">
                        <p class="mb-0" data-bs-toggle="tooltip" data-bs-title="Default tooltip">checked&nbsp;in</p>
                        <span class="fs-1 fw-bold" x-text="checkedIn">-</span>
                    </li>
                    <li class="list-group-item">
                        <p class="mb-0" data-bs-toggle="tooltip" data-bs-title="Default tooltip">remaining</p>
                        <span class="fs-1 fw-bold" x-text="remaining">-</span>
                    </li>
                </ul>
            </div>

            <div class="p-4">
                <table class="table table-bordered w-auto">
                    <tr class="table-dark sticky-top" style="top: 150px">
                        <th>Check-in</th>
                        <th>Last</th>
                        <th>First</th>
                        <th>Special</th>
                        <th>Notes</th>
                        <th>Where</th>
                    </tr>
                    <template x-for="row in ready" :key="row.id">
                        <tr :class="row.discount_code ? 'fw-bold' : ''">
                            <td>
                                <template x-for="i in row.quantity">
                                    <input @click="handleCheck(row, $event)" :class="(i == row.quantity) ? '' : 'me-3'" type="checkbox" />
                                </template>
                            </td>
                            <td x-text="row.last"></td>
                            <td x-text="row.first"></td>
                            <td>
                                <span class="badge rounded-pill text-bg-warning fs-6 me-2" x-show="row.welcome">🥳&nbsp;&nbsp;First-timer!</span>
                                <code x-text="row.discount_code"></code>    
                            </td>
                            <td>
                                <a @click.prevent="row.notes = ! row.notes" class="text-decoration-none" href="#">🗒️</a>
                                <input x-show="row.notes" class="form-control py-0 px-1" style="font-size: smaller" type="text" />
                            </td>
                            <td x-text="row.where"></td>
                        </tr>
                    </template>
                </table>

                <div class="mt-5" x-show="done.length">
                    <h2 class="mb-3">Checked in</h2>

                    <table class="table table-bordered w-auto">
                        <tr class="table-dark">
                            <th>Check-in</th>
                            <th>Last</th>
                            <th>First</th>
                            <th>Special</th>
                            <th>Notes</th>
                            <th>Where</th>
                        </tr>
                        <template x-for="row in done" :key="row.id">
                            <tr :class="row.discount_code ? 'fw-bold' : ''">
                                <td>
                                    <template x-for="i in row.quantity">
                                        <input @click="handleCheck(row, $event)" checked :class="(i == row.quantity) ? '' : 'me-3'" type="checkbox" />
                                    </template>
                                </td>
                                <td x-text="row.last"></td>
                                <td x-text="row.first"></td>
                                <td>
                                    <code x-text="row.discount_code"></code>    
                                </td>
                                <td>
                                    <a @click.prevent="row.notes = ! row.notes" class="text-decoration-none" href="#">🗒️</a>
                                    <input x-show="row.notes" class="form-control py-0 px-1" style="font-size: smaller" type="text" />
                                </td>
                                <td x-text="row.where"></td>
                            </tr>
                        </template>
                    </table>
                </div>
            </div>
        </div>

        <script>
            function load() {
                return {
                    sold: @json($sold),
                    totalAvailable: 49,

                    list: @json($rows),

                    get available() {
                        let num = this.totalAvailable - this.sold;
                        return num == 0 ? 'SOLD&nbsp;OUT' : num;
                    },
                    get checkedIn() { return this.list.reduce((sum, row) => sum + row.checked_in, 0)},
                    get remaining() { return this.sold - this.checkedIn },
                    get soldOut() { return this.totalAvailable == this.sold },

                    get ready() { return this.list.filter(i => ! i.done) },
                    get done() {
                        return this.list.filter(i => i.done);
                    },

                    handleCheck(row, event) {
                        if (event.target.checked)
                            row.checked_in++;
                        else
                            row.checked_in--;

                        // console.log('checked_in ' + row.checked_in + ' vs quantity ' + row.quantity);
                        row.done = (row.checked_in == row.quantity);
                        // console.log('row done', row.done);
                    }
                }
            }
        </script>

    {{--  Upload. --}}
    @else
        <div class="p-5">
            <h1>Upload file</h1>

            @if ($errors->any())
                <div class="alert alert-danger d-inline-block">
                    {{ $errors->first() }}
                </div>
            @endif

            <p>This is the check-in tool for Story Parlor. It accepts a CSV file from Squarespace.</p>
            <ol>
                <li>
                    <a class="btn btn-sm btn-success" href="https://mandarin-synthesizer-k3yg.squarespace.com/config/commerce/orders" target="_blank">Open the Orders page in Squarespace</a>
                </li>
                <li>Click <span class="fw-bold">DOWNLOAD CSV</span> in the upper right</li>
                <li>Under Product, select <span class="fw-bold">Specific product</span></li>
                <li>Type in the event name and select tonight's event</li>
                <li>Click <span class="fw-bold">DOWNLOAD</span> in the top right</li>
                <li>Open the Files app, find the file likely named <code>orders</code> and rename it to avoid confusion</li>
                <li>Select that file here</li>
            </ol>

            <form x-data="{ fileSelected: false, processing: false }"
                @submit="processing = true"
                class="mt-2 d-inline-block"
                enctype="multipart/form-data"
                method="post">
                @csrf
                <input @change="fileSelected = $event.target.files.length > 0" 
                    class="form-control form-control-lg mb-4"
                    name="file"
                    type="file">

                <button :disabled="!fileSelected || processing" 
                    x-text="processing ? 'Processing...' : 'Process'"
                    :disabled="disabled"
                    class="btn btn-primary">Process</button>
            </form>
        </div>
    @endif
@endsection