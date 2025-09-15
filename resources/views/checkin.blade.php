<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

        <style>
            input[type="checkbox"] {
                transform: scale(1.5);
            }
            input[type="text"] {
                min-width: 300px;
            }
        </style>
        <title>{{ $event ?? config('app.name') }}</title>
     </head>
    <body @if ($rows)x-data="load"@endif>
        <nav class="navbar bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand text-white fw-bold" href="/">
                    <span class="me-1">🎭</span>
                    {{ config('app.name') }}
                </a>
            </div>
        </nav>

        {{-- Checkin. --}}
        @if ($rows)
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
                            let done = this.list.filter(i => i.done);
                            console.log('done', done.length);
                            return done;
                        },

                        handleCheck(row, event) {
                            if (event.target.checked)
                                row.checked_in++;
                            else
                                row.checked_in--;

                            // console.log('checked_in ' + row.checked_in + ' vs quantity ' + row.quantity);
                            row.done = (row.checked_in == row.quantity);
                            console.log('row done', row.done);
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

                <form class="mt-2 d-inline-block" enctype="multipart/form-data" method="post" onchange="document.getElementById('process').removeAttribute('disabled')">
                    @csrf
                    <input class="form-control form-control-lg mb-4" name="file" type="file">

                    <button class="btn btn-primary" disabled id="process">Process</button>
                </form>
            </div>
        @endif
    </body>
</html>