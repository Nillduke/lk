@extends('layout.default')
@section('head_meta')
<style>

        body{
            background-color: #eee;
        }
        .unsub-sec{
            background: #fff;
            padding: 30px;
        }
        .main{
            margin-top: 5rem;
        }
        h2{
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .mailinfo{
            text-align: center;
            font-size: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 2rem;
        }
        .mailinfo span{
            font-weight: bold;
        }
        .feedbacktext{
            margin: 20px auto;
            font-size: 0.9rem;
            text-align: center;
        }
        .form-control{
            width: 60% !important;
        }
        .form-group, .formcont, .succmsg{
            text-align: center;
            text-align: -webkit-center;
        }
        .succmsg span{
            font-weight: bold;
        }
        .succmsg{
            font-size: 1.3rem;
        }
        #unsubscribefrm{
            text-align: center;
        }
        #feedback{
            margin-top: 30px;
        }
        #frmsubmit{
            margin: 20px 0px;
        }
        h3{
            text-align: center;
            font-size: 2rem;
            color: red;
            font-weight: 600;
        }
        .content{
            /* height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center; */
        }
    
</style>
@endsection
@section('content')
<div class="shared-unsubscribe">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="main">
                    <div class="content">
                        
                        <div class="unsub-sec">
                        @php 
                                if ($isUnsubscribed) {@endphp
                                    <p class="succmsg"><span>{{ $email }}</span> is successfully unsubscribed.</p>
                                    @php } else {@endphp
                            <p class="mailinfo">
                                <span>{{ ($email) }} </span><br>
                                is subscribed to our mailing list(s). 
                            </p>
                            <h3>Unsubscribe from our mailing list.</h3>                            
                            <div id="formcont">
                            <p class="feedbacktext">To help us improve our services, we would be grateful if you could tell us why:</p>
                            <p id="form-unsubscribe">                               
                                        <form role="form" name="unsubscribefrm" id="unsubscribefrm">
@csrf
                                        <input type="hidden" name="email_id" value="{{ $email }}" />
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                            <select class="form-control" id="reasons" name="feedback1">
                                                <option value="">Please select reason</option>
                                                <option value="Your emails are not relevant to me">Your emails are not relevant to me</option>
                                                <option value="Your emails are too frequent">Your emails are too frequent</option>
                                                <option value="I don't remember signing up for this">I don't remember signing up for this</option>
                                                <option value="I no longer want to receive these emails">I no longer want to receive these emails</option>
                                                <option value="The emails are spam and should be reported">The emails are spam and should be reported</option>
                                                <option value="others">Others</option>
                                            </select>
                                        </div>
                                            <div class="form-group" id="feedbacksec">
                                                <textarea name="feedback" class="form-control" id="feedback" rows="5" placeholder="Please your feedback here."></textarea>
                                            </div>
                                            <button class="btn btn-primary" id="frmsubmit">Unsubscribe</button>
                                        </form>
                            </p>
                            </div>
                            @php }@endphp
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('footer_script')

    
    <script>
        $(document).ready(function(){
            var emailId = '{{ $email }}';
            $("#feedbacksec").css('display','none');
            /**Onchange */
            $("#reasons").on('change', function(e) {
                console.log(this.value);
                if (this.value == 'others') {
                    $("#feedbacksec").show(300); 
                } else {
                    $("#feedbacksec").hide(300);
                }
            })
            /**submit */
            $("#unsubscribefrm").on('submit', function(e){
                e.preventDefault();

            // CSRF Hash
            var csrfName = '{{ '_token' }}'; // CSRF Token name
            var csrfHash = $('input[name="_token"]').val(); // CSRF hash

            // AJAX request
            $.ajax({
                url: "{{ site_route('unsubscribe_process') }}",
                method: 'POST',
                data: $("#unsubscribefrm").serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $("#frmsubmit").html('Unsubscribing...');
                    $("#frmsubmit").attr('disabled', 'disabled');
                },
                success: function(data){
                    console.log('Success:');
                    $(".unsub-sec").html(`<p class="succmsg"><span>${emailId}</span> is successfully unsubscribed.</p>`);
                },
                error: function(err) {
                    console.log('Error:', err);
                },
                complete: function() {
                    console.log('Completed:');
                    $("#frmsubmit").html('Unsubscribe...');
                    $("#frmsubmit").attr('disabled', false);
                }
            });
            });
        });
    </script>

@endsection
