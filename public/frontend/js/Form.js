'use strict';
//import countries from "../assets/countries.json";

const e = React.createElement;
class Input extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      message:'',
      errors:{} 
    };
 //   this.handleFieldChange = this.handleFieldChange.bind(this);
    this.renderErrorFor = this.renderErrorFor.bind(this);
  }

renderErrorFor () {
  if (this.props.isError) {
    return (
      <span className='help-block'>
        <strong>{this.props.error}</strong>
      </span>
    )
  }
}
  render() {
      return(
           <div className={`col-md-6 col-sm-6 ${this.props.isError ? 'has-error' : ''}`}>
                      <label htmlFor={this.props.name}>{this.props.lable}</label>
                      <div className="input-group">
                        <span className="input-group-addon"><i className={this.props.icon}></i></span>
                        <input type="text" id={this.props.id} placeholder={this.props.lable} name={this.props.name} 
                           className="form-control"/>
                    
                      </div>
                      {this.renderErrorFor()}
             </div>	
         
      )
  }
}
/* Textarea COMPONENT STARTS */ 
class Textarea extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
       message:'',
      errors:{} 
    };
    this.renderErrorFor = this.renderErrorFor.bind(this);
}

renderErrorFor () {
  if (this.props.isError) {
    return (
      <span className='help-block'>
        <strong>{this.props.error}</strong>
      </span>
    )
  }
}
  render() {
      return(
       	<div className={`col-md-${this.props.divcol} col-sm-${this.props.divcol} ${this.props.isError ? 'has-error' : ''}`}>
                      <label htmlFor={this.props.name}>{this.props.lable}</label>
                      <div className="input-group">
                        <span className="input-group-addon"><i className={this.props.icon}></i></span>
                        <textarea rows={this.props.rows} id={this.props.id} placeholder={this.props.lable} name={this.props.name} 
                           className="form-control"></textarea>
                    
                      </div>
                      {this.renderErrorFor()}
        </div>
		)
  }
}
/* FORM COMPONENT STARTS */ 
class Select extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
       message:'',
       options:[{'name':"Pravin",'value':"Mehetre"},{'name':"Kishan",'value':"Nikaju"}],
      errors:{} 
    };
   
    this.renderErrorFor = this.renderErrorFor.bind(this);
}
componentWillMount() {
 /* axios.get('../Api/countries').then(response =>{
    //console.log(response.data);  
    this.setState({
      options:response.data
    })
  }).catch(error => {
        console.log(error);
      
  })*/
  fetch('../assets/countries.json').then(response => response.json())
  .then(response => 
    this.setState({
    options:response
  })
  )
}
renderErrorFor () {
  if (this.props.isError) {
    return (
      <span className='help-block'>
        <strong>{this.props.error}</strong>
      </span>
    )
  }
}
  render() {
      return(
       	<div className={`col-md-${this.props.divcol} col-sm-${this.props.divcol} ${this.props.isError ? 'has-error' : ''}`}>
                      <label htmlFor={this.props.name}>{this.props.lable}</label>
                      <div className="input-group">
                        <span className="input-group-addon"><i className={this.props.icon}></i></span>
                        <select id={this.props.id} name={this.props.name} className="form-control">
                        <option value="">Select Country</option>
                        {this.state.options.map(function(option, index) {
                          return <option value={`${option.phonecode}-${option.nicename}`} key={index}>{option.nicename}</option>
                        })}
                          </select>
                    
                      </div>
                      {this.renderErrorFor()}
        </div>
		)
  }
}
/* FORM COMPONENT STARTS */ 
class Form extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      message:'',
      firstParam:'6',
      secParam:'2',
      operation:'+',
      answer:'',
      captchans: '',
      queString:'',
      isValidationPassed:false,
      loading: false,
      errors:{} 
    };
    this.handleSubmit = this.handleSubmit.bind(this);
    this.hasErrorFor = this.hasErrorFor.bind(this);
    this.createQuestion = this.createQuestion.bind(this);
    this.handleOnChange = this.handleOnChange.bind(this); 
}
componentDidMount(){
  this.createQuestion();

}
createQuestion (){
  const a = Math.floor(Math.random() * 10) + 1;
  const b = Math.floor(Math.random() * 10) + 1;
  this.setState({
    firstParam: a,
      secParam: b,
      queString : `${a} + ${b}`,
    answer : a + b
  })
 // return Math.round() + Math.round()
}
handleOnChange(){
  const given = event.target.value; 
  //console.log(given);
  var ispassed = false;
    if (given == this.state.answer) {
      ispassed = true;
    }
  this.setState({
    captchans: given,
    isValidationPassed: ispassed
  })
}
hasErrorFor (field) {
  return !!this.state.errors[field]
}


handleSubmit(e){
  e.preventDefault();
  this.setState({
   
    loading: true,
  })
  let myForm = document.getElementById('contact_frm2');
  let formData = new FormData(myForm);
  formData.append('form_for2','Contact Us');
  axios.post('../Api/contact',formData).then(response =>{
    this.setState({
      message: response.data.message,
      loading: false,
      errors: response.data.errors ? response.data.errors : {}
    })
    if(response.data.flagSuccess){
      //console.log('flag success true');
      window.setTimeout(function(){
        window.location = 'https://www.24marketreports.com/contact-us/thanks';
      },3000)
    
    }
  }).catch(error => {
       // console.log(error);
        this.setState({
          message: `<div class="alert alert-danger">${error}</div>`,
          loading: false,
        })
  })
  }


  render(){
   const {errors, loading,queString,isValidationPassed } = this.state;
   let content;

      content = <div>
      <div dangerouslySetInnerHTML={{__html: this.state.message}} />
         <form id="contact_frm2" className="form-horizontal bv-form" onSubmit={this.handleSubmit}>  
         <p style={{'display':'none'}}>Leave This Empty: <input type="text" name="url2"/></p>
      <div className="row chck-frow">
        <Input lable="Name" name="full_name2" key="name" id="name" divcol="6" icon="glyphicon glyphicon-user" isError={this.hasErrorFor('full_name2')} error={errors.full_name2}/>
        <Input lable="Email" name="email_id2" key="emailID" id="email_id2" divcol="6" icon="glyphicon glyphicon-user" isError={this.hasErrorFor('email_id2')} error={errors.email_id2}/>
          
      </div>
      <div className="row chck-frow">
        <Input lable="Company" name="company" key="company" id="company" divcol="6" icon="fa fa-building-o" isError={this.hasErrorFor('company')} error={errors.company}/>
        
        <Input lable="Contact No" name="contact_no2" key="contact_no" divcol="6" id="contact_no" icon="glyphicon glyphicon-earphone" isError={this.hasErrorFor('contact_no2')} error={errors.contact_no2}/>				
      </div>
     
      <div className="row chck-frow">
      <Select lable="Country" name="country" key="country" id="country" icon="glyphicon glyphicon-map-marker" divcol="6" isError={this.hasErrorFor('country')} error={errors.country}/>
        <Input lable="City" name="city2" key="city" id="city2" divcol="6" icon="glyphicon glyphicon-map-marker" isError={this.hasErrorFor('city2')} error={errors.city2}/>				
      </div>
      <div className="row chck-frow">
        <Textarea rows="3" lable="Message" name="message2" divcol="12" key="message" id="messagebox" icon="glyphicon glyphicon-pencil" isError={this.hasErrorFor('message2')} error={errors.message2}/>
          
      </div>
     
      <div className="row chck-frow">
      <div className="col-md-6 col-sm-6 row">

<label htmlFor="question" className="col-md-12"> Security Question*</label>
<div className="col-md-6">
<div className="sec-code">
  
  <span id="question123" style={{'fontSize':'24px','fontFamily':'Segoe, &quot;Segoe UI&quot;, &quot;DejaVu Sans&quot;,&quot;Trebuchet MS&quot;, Verdana, sans-serif'}}>{queString}</span>
</div>
</div>

<div className="col-md-6 col-sm-6">					
  <div className="cus-input">
    <input type="text" className="form-control" id="ans" placeholder="Enter Security Answer" name="ans"
    onChange={this.handleOnChange} autoComplete="off"/>
    {!this.state.captchans && !isValidationPassed ? <div id="message" className="msgerr">Please verify.</div> : ''}
    {this.state.captchans && isValidationPassed ? <div id="success" className="msgok">Validation complete</div>: ''}
     {this.state.captchans && !isValidationPassed ? <div id="fail" className="msgerr">Validation failed</div>: ''}	
  </div>
</div>

</div>
      <div className="col-md-6 col-sm-6">
        <button type="submit" className="contactus-btn" name="cont_submit2" id="cont_submit" value="Contact US"
        disabled={!isValidationPassed}>
        {loading ? <span dangerouslySetInnerHTML={{ __html: '<i className="fa fa-cog fa-spin fa-fw"></i> Submitting' }}/> : 'Contact US'}</button>
      </div>
        
      </div>
        </form>
        </div>  ;
    return (
    <div>{content}</div>
    );
  }  
    }
window.Form = Form;
// Find all DOM containers, and render Like buttons into them.
ReactDOM.render(<Form />, document.getElementById('contactform'));